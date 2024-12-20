<?php

namespace Warkhosh\Component\Traits;

use Warkhosh\Component\Collection\Interfaces\Arrayable;
use Warkhosh\Component\Collection\Interfaces\Jsonable;
use JsonSerializable;

/**
 * CollectionMethod
 *
 * Методы для интерфейсов: Arrayable, Jsonable, Iterator, ArrayAccess, Countable, JsonSerializable
 *
 * @package Warkhosh\Component\Traits
 */
trait CollectionMethod
{
    /**
     * Интерфейс Arrayable
     *
     * @note Обеспечивает преобразования значений простой массив
     */

    /**
     * Получить список элементов в виде простого массива
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->data);
    }

    /**
     * Интерфейс Jsonable
     *
     * @note Обеспечивает преобразования значений в JSON
     */

    /**
     * Get the collection of items as JSON
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Интерфейс Iterator
     *
     * @note Для внешних итераторов или объектов, которые могут повторять себя изнутри
     */

    /**
     * Перемотка Итератора к первому элементу
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @note  Любое возвращаемое значение игнорируется
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Возвращает текущий элемент
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @note  Может возвращать любой тип!
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * Возврат ключа текущего элемента
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @note  Скаляр при успешном выполнении или null при сбое
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * Перейти к следующему элементу
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @note  Любое возвращаемое значение игнорируется
     *
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Проверяет, действительна ли текущая позиция
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @note Возвращает true при успешном выполнении или false при сбое
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->data[$this->key()]);
    }

    /**
     * Alias for the "offsetSet" method
     *
     * @param float|int|string $key
     * @param mixed $value
     * @return $this
     */
    public function add(float|int|string $key, mixed $value = null): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Интерфейс ArrayAccess
     *
     * @note Обеспечивает доступ к объектам как к массиву
     */

    /**
     * Проверка существования значения в массиве по указанному ключу
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @note Данный метод исполняется, когда используется функция isset() или функция empty() для объекта
     *
     * @param mixed $offset смещение для проверки
     * @return bool возвращает true в случае успешного завершения или false в случае возникновения ошибки
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Получения значения из массива по ключу
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @note Работа с многомерным набором будет вызывать исключение и точка
     *
     * @param mixed $offset смещение для извлечения
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];

        } else {
            if (isset($this->useDefault) && $this->useDefault) {
                return $this->default;
            }

            trigger_error("It is impossible to find the specified key");
        }

        return $this->default;
    }

    /**
     * Установки значения в массиве по ключу
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @note Работа с многомерным набором будет вызывать исключение и точка
     *
     * @param mixed $offset смещение для присвоения значения
     * @param mixed $value значение для установки
     * @return void
     */
    public function offsetSet(mixed $offset = null, mixed $value = null): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Удаление значения из массива по ключу
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Интерфейс Countable
     *
     * @note Обеспечивает подсчет количество элементов объекта
     */

    /**
     * Количество элементов объекта
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return integer Возвращаемое значение приводится к целому числу
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Интерфейс JsonSerializable
     *
     * @note Задает данные, которые должны быть сериализованы в JSON
     */

    /**
     * Convert the object into something JSON serializable
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();

            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);

            } elseif ($value instanceof Arrayable) {
                return $value->toArray();

            } else {
                return $value;
            }
        }, $this->data);
    }
}
