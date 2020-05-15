<?php

namespace Warkhosh\Component\Std;

use Warkhosh\Component\Collection\Interfaces\Arrayable;
use Warkhosh\Component\Collection\Interfaces\Jsonable;
use JsonSerializable;

/**
 * BaseDataProvider (поставщик данных)
 *
 * Класс для хранения в нем данных и использования универсальных методов при работе с ними
 *
 * @note    Не реализует Iterator ( foreach и while с объектом не выдадут ошибку, но и не отработают )
 * @note    ArrayObject используется для декоративного применения, чтобы phpstorm не подсвечивал magic переменные
 *
 * @package Warkhosh\Component\Std
 */
class BaseDataProvider extends \ArrayObject implements Arrayable
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Флаг для использования значения по умолчанию вместо бросания исключений при обращении к недопустимым значениям
     *
     * @var bool
     */
    protected $useDefault = true;

    /**
     * @var null
     */
    protected $default = null;

    /**
     * @param array|Arrayable|Jsonable|string|int|float|null $input
     */
    public function __construct($input = [])
    {
        $this->data = $this->getArrayItems($input);
    }

    /**
     * Результаты массива, элементов из коллекции или Arrayable
     *
     * @param mixed $input
     * @return array
     */
    protected function getArrayItems($input)
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

        } elseif ($input instanceof \Traversable) {
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
     * @return $this
     */
    public function asort()
    {
        asort($this->data);

        return $this;
    }

    /**
     * Получить количество общедоступных свойств ArrayObject
     *
     * @link  https://php.net/manual/en/arrayobject.count.php
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Сортировать записи по ключам
     *
     * @link https://php.net/manual/en/arrayobject.ksort.php
     * @return $this
     */
    public function ksort()
    {
        krsort($this->data);

        return $this;
    }

    /**
     * Сортировать массив, используя алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natsort.php
     * @return $this
     */
    public function natsort()
    {
        natsort($this->data);

        return $this;
    }

    /**
     * Сортировать массив, используя регистронезависимый алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natcasesort.php
     * @return $this
     */
    public function natcasesort()
    {
        natcasesort($this->data);

        return $this;
    }

    /**
     * Возвращает, существует ли указанный индекс
     *
     * @link https://php.net/manual/en/arrayobject.offsetexists.php
     * @param mixed $index
     * @return bool true if the requested index exists, otherwise false
     */
    public function offsetExists($index)
    {
        return key_exists($index, $this->data);
    }

    /**
     * Возвращает значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetget.php
     * @param mixed $index
     * @return mixed The value at the specified index or false.
     */
    public function offsetGet($index)
    {
        return key_exists($index, $this->data) ? $this->data[$index] : $this->default;
    }

    /**
     * Устанавливает новое значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetset.php
     * @param mixed $index
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($index, $value)
    {
        $this->data[$index] = $value;

        return $this;
    }

    /**
     * Удаляет значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetunset.php
     * @param integer | string $index
     * @return $this
     */
    public function offsetUnset($index)
    {
        if (! is_null($index) && key_exists($index, $this->data)) {
            unset($this->data[$index]);
        }

        return $this;
    }

    /**
     * Генерирует пригодное для хранения представление
     *
     * @link https://php.net/manual/en/arrayobject.serialize.php
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Сортировать записи, используя пользовательскую функцию для сравнения элементов и сохраняя при этом связь ключ/значение
     *
     * @link https://php.net/manual/en/arrayobject.uasort.php
     * @param callable $function
     * @return $this
     */
    public function uasort($function)
    {
        uasort($this->data, $function);

        return $this;
    }

    /**
     * Сортировать массив по ключам, используя пользовательскую функцию для сравнения
     *
     * @link https://php.net/manual/en/arrayobject.uksort.php
     * @param callable $function
     * @return $this
     */
    public function uksort($function)
    {
        uksort($this->data, $function);

        return $this;
    }

    /**
     * Добавляет значение в конец массива
     *
     * @link  https://php.net/manual/en/arrayobject.append.php
     * @param mixed $value
     * @return $this
     */
    public function append($value)
    {
        array_push($this->data, $value);

        return $this;
    }

    /**
     * Устанавливает заданный ключ и значение
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function put($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Создаёт копию ArrayObject как массив
     *
     * @link  https://php.net/manual/en/arrayobject.getarraycopy.php
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->data;
    }

    /**
     * @param string $index
     * @param mixed  $value
     * @return void
     */
    public function __set($index, $value)
    {
        $this->data[$index] = $value;
    }

    /**
     * @param string $index
     * @return mixed
     */
    public function __get($index)
    {
        if ($this->useDefault) {
            return key_exists($index, $this->data) ? $this->data[$index] : $this->default;
        }

        return $this->data[$index];
    }

    /**
     * @param string | integer $index
     * @return mixed
     *
     * public function get($index)
     * {
     * if ($this->useDefault) {
     * return VarArray::get($index, $this->data, $this->default);
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
    public function __unset($index)
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
    public function toArray()
    {
        return array_map(function($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->data);
    }

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param boolean $use
     * @return $this
     */
    public function setUseDefault($use)
    {
        $this->useDefault = (bool)$use;

        return $this;
    }

    /**
     * Возращает результат проверки: не является ли пустой выборкой.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Возращает результат проверки: является ли выборка пустой или нет.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Добавить указанные данные к существующим ( перезапишет присутствующие значения )
     *
     * @param array $input
     * @return $this
     */
    public function merge($input = [])
    {
        $this->data = array_merge($this->data, $this->getArrayItems($input));

        return $this;
    }

    /**
     * Определяет, существует ли элемент в коллекции по ключу.
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        return key_exists($key, $this->data);
    }

    /**
     * Получите колекцию с ключами от предметов коллекции.
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->data));
    }

    /**
     * Удаление элемента по ключу.
     *
     * @param string|array $keys
     * @return $this
     */
    public function forget($keys)
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
    public function clone()
    {
        return clone $this;
    }

    /**
     * Преобразует все значения из многомерного в одно измерение
     *
     * @param int $depth
     * @return static
     */
    public function flatten($depth = INF)
    {
        return new static($this->getFlatten($this->toArray(), $depth));
    }

    private function getFlatten($array = [], $depth = INF)
    {
        if (! is_array($array)) {
            return [];
        }

        return array_reduce($array, function($result, $item) use ($depth) {
            $item = $item instanceof \Warkhosh\Component\Std\BaseDataProvider ? $item->toArray() : $item;

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
    public function collapse()
    {
        return new static($this->getCollapse($this->toArray()));
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param array $array
     * @return array
     */
    private static function getCollapse(array $array)
    {
        $results = [];

        foreach ($array as $key => $values) {
            if ($values instanceof \Warkhosh\Component\Std\BaseDataProvider) {
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
    public function all()
    {
        return $this->toArray();
    }

    /**
     * Выбрасывает показ всех значений и завершение сценария.
     *
     * @return void
     */
    public function dd()
    {
        var_dump($this->all());
        die;
    }

    /**
     * Преобразуйте значений в ее строковое JSON представление.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Добавление переданного исключения
     *
     * @param $exception
     * @return $this
     */
    public function addException($exception)
    {
        if ($exception instanceof \Throwable) {
            $this->data['exception_message'] = $exception->getMessage();
            $this->data['exception_code'] = $exception->getCode();
            $this->data['exception_file'] = $exception->getFile() . "(" . $exception->getLine() . ")";
            $this->data['exception_line'] = $exception->getLine();
            $this->data['exception_trace'] = $exception->getTraceAsString();
        } else {
            $this->data['exception_message'] = (string)$exception;
        }

        return $this;
    }
}