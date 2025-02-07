<?php

namespace Warkhosh\Component\Std;

use Warkhosh\Component\Collection\Interfaces\Arrayable;
use Warkhosh\Component\Collection\Interfaces\Jsonable;
use Warkhosh\Exception\ImprovedExceptionInterface;
use JetBrains\PhpStorm\NoReturn;
use JsonSerializable;
use ArrayObject;
use Traversable;
use Throwable;
use stdClass;

/**
 * BaseDataProvider (поставщик данных)
 *
 * Класс для хранения в нем данных и использования универсальных методов при работе с ними
 *
 * @note Не реализует Iterator (foreach и while с объектом не выдадут ошибку, но и не отработают)
 * @note ArrayObject изначально использовался для декоративного применения, чтобы phpstorm не подсвечивал magic переменные
 * @note ArrayObject в версии 1.2 заменен на stdClass, но его методы используется
 *
 * @package Warkhosh\Component\Std
 * @version 1.2
 */
class BaseDataProvider extends stdClass implements Arrayable, DataProviderInterface
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Флаг для использования значения по умолчанию вместо бросания исключений при обращении к недопустимым значениям
     *
     * @var bool
     */
    protected bool $useDefault = true;

    /**
     * @var mixed
     */
    protected mixed $default = null;

    /**
     * @param array|Arrayable|float|int|Jsonable|string|null $input
     */
    public function __construct(array|Arrayable|float|int|Jsonable|string|null $input = [])
    {
        $this->data = $this->getArrayItems($input);
    }

    /**
     * Результаты массива, элементов из коллекции или Arrayable
     *
     * @param mixed $input
     * @return array
     */
    protected function getArrayItems(mixed $input): array
    {
        if (is_array($input)) {
            return $input;

        } elseif ($input instanceof self) {
            return $input->all();

        } elseif ($input instanceof Arrayable) {
            return $input->toArray();

        } elseif (is_object($input) && method_exists($input, 'toArray')) {
            return $input->toArray();

        } elseif ($input instanceof Jsonable) {
            return json_decode($input->toJson(), true);

        } elseif ($input instanceof JsonSerializable) {
            return $input->jsonSerialize();

        } elseif ($input instanceof Traversable) {
            return iterator_to_array($input);
        }

        if (is_null($input) || is_bool($input)) {
            $input = [];
        }

        return (array)$input;
    }

    /**
     * Поддержка методов ArrayObject
     */

    /**
     * Сортировать записи по значению
     *
     * @link https://php.net/manual/en/arrayobject.asort.php
     *
     * @param int $flags
     * @return $this
     */
    public function asort(int $flags = SORT_REGULAR): static
    {
        asort($this->data, $flags);

        return $this;
    }

    /**
     * Получить количество общедоступных свойств ArrayObject
     *
     * @link  https://php.net/manual/en/arrayobject.count.php
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Сортировать записи по ключам
     *
     * @link https://php.net/manual/en/arrayobject.ksort.php
     *
     * @param int $flags
     * @return $this
     */
    public function ksort(int $flags = SORT_REGULAR): static
    {
        krsort($this->data, $flags);

        return $this;
    }

    /**
     * Сортировать массив, используя алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natsort.php
     *
     * @return $this
     */
    public function natsort(): static
    {
        natsort($this->data);

        return $this;
    }

    /**
     * Сортировать массив, используя регистронезависимый алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natcasesort.php
     *
     * @return $this
     */
    public function natcasesort(): static
    {
        natcasesort($this->data);

        return $this;
    }

    /**
     * Возвращает, существует ли указанный индекс
     *
     * @link https://php.net/manual/en/arrayobject.offsetexists.php
     *
     * @param mixed $key
     * @return bool true if the requested index exists, otherwise false
     */
    public function offsetExists(mixed $key): bool
    {
        return key_exists($key, $this->data);
    }

    /**
     * Возвращает значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetget.php
     *
     * @param mixed $key
     * @return mixed The value at the specified index or false
     */
    public function offsetGet(mixed $key): mixed
    {
        return key_exists($key, $this->data) ? $this->data[$key] : $this->default;
    }

    /**
     * Устанавливает новое значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetset.php
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function offsetSet(mixed $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Удаляет значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetunset.php
     *
     * @param mixed $key
     * @return $this
     */
    public function offsetUnset(mixed $key): static
    {
        if (! is_null($key) && key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Генерирует пригодное для хранения представление
     *
     * @link https://php.net/manual/en/arrayobject.serialize.php
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->data);
    }

    /**
     * Сортировать записи, используя пользовательскую функцию для сравнения элементов и сохраняя при этом связь ключ/значение
     *
     * @link https://php.net/manual/en/arrayobject.uasort.php
     *
     * @param callable $callback
     * @return $this
     */
    public function uasort(callable $callback): static
    {
        uasort($this->data, $callback);

        return $this;
    }

    /**
     * Сортировать массив по ключам, используя пользовательскую функцию для сравнения
     *
     * @link https://php.net/manual/en/arrayobject.uksort.php
     *
     * @param callable $callback
     * @return $this
     */
    public function uksort(callable $callback): static
    {
        uksort($this->data, $callback);

        return $this;
    }

    /**
     * Добавляет значение в конец массива
     *
     * @link  https://php.net/manual/en/arrayobject.append.php
     *
     * @param mixed $value
     * @return $this
     */
    public function append(mixed $value): static
    {
        $this->data[] = $value;

        return $this;
    }

    /**
     * Устанавливает заданный ключ и значение
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function put(mixed $key, mixed $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Создаёт копию ArrayObject как массив
     *
     * @link  https://php.net/manual/en/arrayobject.getarraycopy.php
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        return $this->data;
    }

    /**
     * @param string $index
     * @param mixed $value
     * @return void
     */
    public function __set(string $index, mixed $value)
    {
        $this->data[$index] = $value;
    }

    /**
     * @param string $index
     * @return mixed
     */
    public function __get(string $index)
    {
        if ($this->useDefault) {
            return key_exists($index, $this->data) ? $this->data[$index] : $this->default;
        }

        return $this->data[$index];
    }

    /**
     * @param int|string $index
     * @return mixed
     *
     * public function get($index)
     * {
     * if ($this->useDefault) {
     * return getFromArray($index, $this->data, $this->default);
     * }
     *
     * return $this->data[$index];
     * }*/

    /**
     * @param $index
     * @return bool
     */
    public function __isset($index)
    {
        return isset($this->data[$index]);
    }

    /**
     * @param string $index
     * @return void
     */
    public function __unset(string $index)
    {
        unset($this->data[$index]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return print_r($this->data, true);
    }

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
     * @param mixed $default
     * @return $this
     */
    public function setDefault(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param bool $use
     * @return $this
     */
    public function setUseDefault(bool $use): static
    {
        $this->useDefault = $use;

        return $this;
    }

    /**
     * Возвращает результат проверки: не является ли пустой выборкой
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Возвращает результат проверки: является ли выборка пустой или нет
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Добавить указанные данные к существующим (перезапишет присутствующие значения)
     *
     * @param array $input
     * @return $this
     */
    public function merge(array $input = []): static
    {
        $this->data = array_merge($this->data, $this->getArrayItems($input));

        return $this;
    }

    /**
     * Определяет, существует ли элемент в коллекции по ключу
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key): bool
    {
        return key_exists($key, $this->data);
    }

    /**
     * Получите коллекцию с ключами от предметов коллекции
     *
     * @return static
     */
    public function keys(): static
    {
        return new static(array_keys($this->data));
    }

    /**
     * Удаление элемента по ключу
     *
     * @param array|string $keys
     * @return $this
     */
    public function forget(array|string $keys): static
    {
        foreach ((array)$keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Клонирование данных
     *
     * @return $this
     */
    public function clone(): static
    {
        return clone $this;
    }

    /**
     * Преобразует все значения из многомерного в одно измерение
     *
     * @param int $depth
     * @return static
     */
    public function flatten(int $depth = INF): static
    {
        return new static($this->getFlatten($this->toArray(), $depth));
    }

    /**
     * @param array $array
     * @param int $depth
     * @return mixed
     */
    private function getFlatten(array $array = [], int $depth = INF): mixed
    {
        if (! is_array($array)) {
            return [];
        }

        return array_reduce($array, function ($result, $item) use ($depth) {
            $item = $item instanceof DataProviderInterface ? $item->toArray() : $item;

            if (! is_array($item)) {
                return array_merge($result, [$item]);

            } elseif ($depth === 1) {
                return array_merge($result, array_values($item));

            } else {
                return array_merge($result, static::getFlatten($item, $depth - 1));
            }
        }, []);
    }

    /**
     * Сворачивает коллекцию массивов в одну одномерную коллекцию
     *
     * @return static
     */
    public function collapse(): static
    {
        return new static($this->getCollapse($this->toArray()));
    }

    /**
     * Collapse an array of arrays into a single array
     *
     * @param array $array
     * @return array
     */
    private static function getCollapse(array $array): array
    {
        $results = [];

        foreach ($array as $key => $values) {
            if ($values instanceof DataProviderInterface) {
                $values = $values->toArray();

            } elseif (! is_array($values)) {
                $values = [$key => $values];
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->toArray();
    }

    /**
     * Выбрасывает показ всех значений и завершение сценария
     *
     * @return void
     */
    #[NoReturn]
    public function dd(): void
    {
        var_dump($this->all());
        die;
    }

    /**
     * Преобразуйте значений в ее строковое JSON представление
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Добавление значений исключения в поставщике данных
     *
     * @param Throwable $exception
     * @return $this
     */
    public function addException(Throwable $exception): static
    {
        if ($exception instanceof ImprovedExceptionInterface) {
            foreach ($exception->toArray() as $key => $value) {
                $this->offsetSet("exception_{$key}", $value);
            }

            return $this;
        }

        $this->offsetSet("exception_message", $exception->getMessage());

        return $this;
    }
}
