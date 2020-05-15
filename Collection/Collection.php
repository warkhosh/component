<?php

namespace Warkhosh\Component\Collection;

use Warkhosh\Component\Collection\Interfaces\Arrayable;
use Warkhosh\Component\Collection\Interfaces\BaseCollection;
use Warkhosh\Component\Collection\Interfaces\Jsonable;
use Warkhosh\Component\Traits\CollectionMethod;
use JsonSerializable;
use Traversable;
use Throwable;
use Closure;

/**
 * Collection
 *
 * @package Warkhosh\Component\Collection
 * @link    http://laravel.su/docs/5.5/collections#method-flatmap
 */
class Collection implements BaseCollection, \Iterator, \ArrayAccess, \Countable, Arrayable, Jsonable, JsonSerializable
{
    use CollectionMethod;

    /**
     * Коллекция элементов
     *
     * @var array
     */
    protected $data = [];

    /**
     * Значения несуществующей переменной.
     *
     * @var null
     */
    protected $default = null;

    /**
     * Флаг использования значения переменной по умолчанию.
     *
     * @note системный флаг и не меняется методами!
     * @var bool
     */
    protected $useDefault = true;

    /**
     * App constructor
     *
     * @param array $input
     */
    public function __construct($input = [])
    {
        $this->data = $this->getArrayableItems($input);
    }

    /**
     * @note Что-бы заработал array_pop()
     * @param array|null $data
     * @return array
     */
    public function __invoke(array $data = null)
    {
        if (is_null($data)) {
            return $this->data;

        } else {
            $this->data = $data;
        }
    }

    /**
     * Преобразуйте коллекцию в ее строковое представление.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Вернет новый экземпляр коллекции с указаными значениями.
     *
     * @param mixed $items
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Вернет коллекцию с указаным значением, если это применимо.
     *
     * @param mixed $value
     * @return static
     */
    public static function wrap($value)
    {
        return $value instanceof self
            ? new static($value)
            : new static(function ($value) {
                if (is_null($value)) {
                    return [];
                }

                return ! is_array($value) ? [$value] : $value;
            });
    }

    /**
     * Получить базовые элементы из данной коллекции, если это применимо.
     *
     * @param array|static $value
     * @return array
     */
    public static function unwrap($value)
    {
        return $value instanceof self ? $value->all() : $value;
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
     * @return mixed
     *
     * public function getDefault()
     * {
     * return $this->default;
     * } */

    // public static function times($number, callable $callback = null)

    /**
     * Получить все элементы в коллекции.
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Получить среднее значение элементов.
     *
     * @param string $key - ключ в значениях которых вычесляем среднее значение
     * @return mixed
     */
    public function avg($key = null)
    {
        if ($count = $this->count()) {
            return $this->sum($key) / $count;
        }

        return 0;
    }

    /**
     * Alias for the "avg" method.
     *
     * @param callable|string $callback
     * @return mixed
     */
    public function average($callback = null)
    {
        return $this->avg($callback);
    }

    /**
     * Сворачивает коллекцию массивов в одну одномерную коллекцию
     *
     * @return static
     */
    public function collapse()
    {
        $results = [];

        foreach ($this->data as $values) {
            if ($values instanceof static) {
                $values = $values->all();

            } elseif (! is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return new static($results);
    }

    /**
     * Добавить элемент в конец коллекции
     *
     * @param mixed $value
     * @return $this
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);

        return $this;
    }

    /**
     * Добавить заданные значения массива или коллекции в конец коллекции
     *
     * @param Traversable $source
     * @return $this
     */
    public function concat(Traversable $source)
    {
        $result = new static($this);

        foreach ($source as $item) {
            $result->push($item);
        }

        return $result;
    }

    /**
     * Получить медиану.
     *
     * @link https://en.wikipedia.org/wiki/Median
     * @param string $key - ключ в значениях которых вычесляем медиану
     * @return mixed
     */
    public function median($key = null)
    {
        $count = $this->count();

        if ($count == 0) {
            return null;
        }

        $values = (isset($key) ? $this->pluck($key) : $this)->sort()->values();

        $middle = (int)($count / 2);

        if ($count % 2) {
            return $values->get($middle);
        }

        return (new static([
            $values->get($middle - 1),
            $values->get($middle),
        ]))->average();
    }

    /**
     * Получить режим заданного ключа.
     *
     * @link https://laravel.com/docs/5.6/collections#method-mode
     * @link https://en.wikipedia.org/wiki/Mode_(statistics)
     * @param mixed $key
     * @return array|null
     */
    public function mode($key = null)
    {
        $count = $this->count();

        if ($count == 0) {
            return null;
        }

        $collection = isset($key) ? $this->pluck($key) : $this;

        $counts = new self;

        $collection->each(function ($value) use ($counts) {
            $counts[$value] = isset($counts[$value]) ? $counts[$value] + 1 : 1;
        });

        $sorted = $counts->sort();

        $highestValue = $sorted->last();

        return $sorted->filter(function ($value) use ($highestValue) {
            return $value == $highestValue;
        })->sort()->keys()->all();
    }

    // public function collapse()

    // public function contains($key, $operator = null, $value = null)

    // public function containsStrict($key, $value = null)

    // public function crossJoin(...$lists)

    /**
     * Дамп коллекции и завершение сценария.
     *
     * @return void
     */
    public function dd()
    {
        $value = $this->all();

        if (is_bool($value)) {
            $value = ($value === true ? "TRUE" : "FALSE");
        }

        $valueType = gettype($value);
        $value = (is_null($value) ? "Null" : $value);
        $value = print_r($value, true);
        $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
        $style = "border:1px solid #900; margin:5px; padding:3px; font-size:10pt; white-space: pre;";
        $title = '<div style="background-color:#990000; color:#FFF; padding:2px;"><strong>Dump and die</strong></div>';
        $valueType = "<div style=\"padding:5px; text-align: left;\">[<b>{$valueType}</b>]</div>";
        $value = "<div style=\"padding:5px; text-align: left;\">{$value}</div>";

        return sprintf(PHP_EOL . "<pre style=\"{$style}\">%s%s</pre>" . PHP_EOL, $title, $valueType . $value);
    }

    // public function dump()

    // public function diff($items)

    // public function diffAssoc($items)

    // public function diffKeys($items)

    /**
     * Перебирает элементы в коллекции и передает каждый элемент в функцию обратного вызова
     *
     * @link https://laravel.com/docs/5.6/collections#method-each
     * @note Если вы хотите остановить итерацию через элементы, вы можете вернуть false из $callback
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->data as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Производит итерацию по элементам коллекции, передавая каждое значение вложенного элемента в заданную функцию обратного вызова
     *
     * @param callable $callback
     * @return static
     */
    public function eachSpread(callable $callback)
    {
        return $this->each(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Метод для проверки того, что все элементы коллекции проходят заданный тест истинности
     *
     * @param string|callable $key
     * @param mixed           $operator
     * @param mixed           $value
     * @return bool
     */
    public function every($key, $operator = null, $value = null)
    {
        if (func_num_args() == 1) {
            $callback = $this->valueRetriever($key);

            foreach ($this->data as $k => $v) {
                if (! $callback($v, $k)) {
                    return false;
                }
            }

            return true;
        }

        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }

        return $this->every($this->operatorForWhere($key, $operator, $value));
    }

    /**
     * Вернет коллекцию с элементами кроме тех, у которых указаны указанные ключи.
     *
     * @param array|string $keys
     * @return static
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Helper::getExcept($keys, $this->data));
    }

    /**
     * Вернет коллекцию с элементами к которым был применен фильтр.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Helper::getWhere($this->data, $callback));
        }

        return new static(array_filter($this->data));
    }

    /**
     * Выполнит $callback функцию если указаное значение будет правда
     *
     * @link https://laravel.com/docs/5.6/collections#method-when
     * @param bool     $value
     * @param callable $callback
     * @param callable $default
     * @return mixed
     */
    public function when($value, callable $callback, callable $default = null)
    {
        if ($value) {
            return $callback($this);

        } elseif ($default) {
            return $default($this);
        }

        return $this;
    }

    /**
     * Выполнит $callback функцию если указаное значение будет ложь.
     *
     * @link https://laravel.com/docs/5.6/collections#method-unless
     * @param bool     $value
     * @param callable $callback
     * @param callable $default
     * @return mixed
     */
    public function unless($value, callable $callback, callable $default = null)
    {
        return $this->when(! $value, $callback, $default);
    }

    /**
     * Возвращает новую коллекцию после фильтрации элементов по заданной паре значений.
     *
     * @link https://laravel.com/docs/5.6/collections#method-where
     * @note Метод использует «свободные» сравнения при проверке значений элементов.
     *       Строка с целым значением будет считаться равной целому числу того же значения.
     *       Используйте метод whereStrict для фильтрации с использованием «строгих» сравнений.
     *
     * @param string $key
     * @param mixed  $operator
     * @param mixed  $value
     * @return static
     */
    public function where($key, $operator, $value = null)
    {
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }

        return $this->filter($this->operatorForWhere($key, $operator, $value));
    }

    /**
     * Get an operator checker callback.
     *
     * @param string $key
     * @param string $operator
     * @param mixed  $value
     * @return Closure
     */
    protected function operatorForWhere($key, $operator, $value)
    {
        return function ($item) use ($key, $operator, $value) {
            $retrieved = Helper::arrayGet($item, $key);

            try {
                switch ($operator) {
                    default:
                    case '=':
                    case '==':
                        return $retrieved == $value;
                    case '!=':
                    case '<>':
                        return $retrieved != $value;
                    case '<':
                        return $retrieved < $value;
                    case '>':
                        return $retrieved > $value;
                    case '<=':
                        return $retrieved <= $value;
                    case '>=':
                        return $retrieved >= $value;
                    case '===':
                        return $retrieved === $value;
                    case '!==':
                        return $retrieved !== $value;
                }
            } catch (Throwable $e) {
                return false;
            }
        };
    }

    /**
     * Фильтрует коллекцию с использованием строгого сравнения
     * Filter items by the given key value pair using strict comparison.
     *
     * @param string $key
     * @param mixed  $value
     * @return static
     */
    public function whereStrict($key, $value)
    {
        return $this->where($key, '===', $value);
    }

    /**
     * Фильтрует коллекцию по заданным ключу/значению, содержащимся в данном массиве
     *
     * @param string $key
     * @param mixed  $values
     * @param bool   $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(function ($item) use ($key, $values, $strict) {
            return in_array(Helper::arrayGet($item, $key), $values, $strict);
        });
    }

    /**
     * Фильтрует коллекцию по строгому сравнениею в заданным ключу/значению, содержащимся в данном массиве
     *
     * все значения сравниваются с использованием строгого сравнения
     * Filter items by the given key value pair using strict comparison.
     *
     * @param string $key
     * @param mixed  $values
     * @return static
     */
    public function whereInStrict($key, $values)
    {
        return $this->whereIn($key, $values, true);
    }

    /**
     * Фильтрует коллекцию по заданным ключу/значению, которые не содержатся в данном массиве
     *
     * @param string $key
     * @param mixed  $values
     * @param bool   $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(function ($item) use ($key, $values, $strict) {
            return in_array(Helper::arrayGet($item, $key), $values, $strict);
        });
    }

    /**
     * Фильтрует коллекцию по строгому сравнениею в заданном ключу/значению, которые не содержатся в данном массиве
     *
     * @param string $key
     * @param mixed  $values
     * @return static
     */
    public function whereNotInStrict($key, $values)
    {
        return $this->whereNotIn($key, $values, true);
    }

    /**
     * Возращает первый элемент в коллекции.
     *
     * @param callable $callback
     * @param mixed    $default
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        return Helper::arrayFirst($this->data, $callback, $default);
    }

    /**
     * Возвращает коллекцию в которой многомерная коллекцию сплющенный в одно измерение.
     *
     * @link https://laravel.com/docs/5.6/collections#method-flatten
     * @param int $depth
     * @return static
     */
    public function flatten($depth = INF)
    {
        if (count($this->data) === 0) {
            return new static([]);
        }

        return new static(Helper::arrayFlatten($this->data, $depth));
    }

    /**
     * Возвращает коллекцию в которой исходные значения перевернуты наоборот
     *
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->data));
    }

    /**
     * Удаление элемента из коллекции по ключу.
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
     * Получить элементы из коллекции по ключу.
     *
     * @param mixed $key     - указанный ключ в колекции
     * @param mixed $default - значение если ключ не найден
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->data[$key];
        }

        return $default instanceof Closure ? $default() : $default;
    }

    /**
     * Возвращает коллекцию сгруппированную по указанному ключу с использованием обратного вызова.
     *
     * @link https://laravel.com/docs/5.6/collections#method-groupby
     * @param callable|string $groupBy
     * @param bool            $preserveKeys - признак сохранения ключей
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        $groupBy = $this->valueRetriever($groupBy);

        $results = [];

        foreach ($this->data as $key => $value) {
            $groupKeys = $groupBy($value, $key);

            if (! is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }

            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int)$groupKey : $groupKey;

                if (! array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = new static;
                }

                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }

        return new static($results);
    }

    /**
     * Возвращает коллекцию сгруппированную по указанному ключу
     *
     * @link https://laravel.com/docs/5.6/collections#method-keyby
     * @note Если несколько элементов имеют один и тот же ключ, в новой коллекции будет отображаться только последний.
     * @param callable | string $keyBy
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);

        $results = [];

        foreach ($this->data as $key => $item) {
            $resolvedKey = $keyBy($item, $key);

            if (is_object($resolvedKey)) {
                $resolvedKey = (string)$resolvedKey;
            }

            $results[$resolvedKey] = $item;
        }

        return new static($results);
    }

    /**
     * Определяет, существует ли элемент в коллекции по ключу.
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if (! $this->offsetExists($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Concatenate values of a given key as a string.
     *
     * @param string $value
     * @param string $glue
     * @return string
     */
    public function implode($value, $glue = null)
    {
        $first = $this->first();

        if (is_array($first) || is_object($first)) {
            return implode($glue, $this->pluck($value)->all());
        }

        return implode($value, $this->data);
    }

    /**
     * удаляет любые значения из исходной коллекции, которых нет в переданном массиве или коллекции
     *
     * @note Полученная коллекция сохранит ключи исходной коллекции
     * @param mixed $items
     * @return static
     */
    public function intersect($items)
    {
        return new static(array_intersect($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Удаляет любые ключи из исходной коллекции, которых нет в переданном массиве или коллекции
     *
     * @param mixed $items
     * @return static
     */
    public function intersectByKeys($items)
    {
        return new static(array_intersect_key($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Возращает результат проверки: не является ли коллекция пустой.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Возращает результат проверки: является ли коллекция пустой или нет.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Определите, является ли данное значение не строкой а исполняемой функцией
     *
     * @param mixed $value
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
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
     * Возвращает последний элемент в массиве.
     *
     * @link https://laravel.com/docs/5.6/collections#method-last
     * @note Если указан $callback вернет последний элемент в коллекции, который проходит данный тест истины
     * @param callable $callback
     * @param mixed    $default
     * @return mixed
     */
    public function last(callable $callback = null, $default = null)
    {
        return Helper::arrayLast($this->data, $callback, $default);
    }

    /**
     * Получить колекцию со значениями из массива текущей коллекции.
     *
     * @note можно указать конкретный ключь в массиве для использования в новом массиве
     * @link https://laravel.com/docs/5.6/collections#method-pluck
     * @param string      $value
     * @param string|null $key
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return new static(Helper::arrayPluck($this->data, $value, $key));
    }

    /**
     * Перебирает коллекцию и передаёт каждое значению в функцию обратного вызова.
     *
     * @note Метод выполняет итерацию по коллекции и передает каждое значение заданному обратному вызову.
     * @note Обратный вызов может изменять элемент и возвращать его, тем самым формируя новую коллекцию измененных элементов
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->data);
        $items = array_map($callback, $this->data, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Производит итерацию по элементам коллекции, передавая каждое значение вложенного элемента в заданную функцию обратного вызова
     *
     * @param callable $callback
     * @return static
     */
    public function mapSpread(callable $callback)
    {
        return $this->map(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Проходит по коллекции и передаёт каждое значение в заданную функцию обратного вызова.
     *
     * @note Эта функция может изменить элемент и вернуть его, формируя таким образом новую коллекцию модифицированных элементов.
     * @note массив "сплющивается" в одномерный
     *
     * @param callable $callback
     * @return static
     */
    public function flatMap(callable $callback)
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Проходит по элементам коллекции и передаёт каждое значение в функцию обратного вызова
     *
     * @note Обратный вызов должен возвращать ассоциативный массив, содержащий одну пару ключ/значение!
     *
     * @link http://laravel.su/docs/5.5/collections#method-mapwithkeys
     * @param callable $callback
     * @return static
     */
    public function mapWithKeys(callable $callback)
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return new static($result);
    }

    /**
     * Получить максимальное значение данного ключа.
     *
     * @note без ключа делает поиск по всему массиву
     * @link https://laravel.com/docs/5.6/collections#method-max
     * @param string|null $key
     * @return mixed
     */
    public function max($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = Helper::arrayGet($item, $key);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    /**
     * Вернуть коллекцию после обхединения с заданными элементами.
     *
     * @note если заданные ключи в массиве числовые, то значения будут добавляться в конец коллекции
     * @param mixed $items
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Получить минимальное значение по указаному ключу.
     *
     * @note без ключа делает поиск по всему массиву
     * @link https://laravel.com/docs/5.6/collections#method-min
     * @param string|null $key
     * @return mixed
     */
    public function min($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = Helper::arrayGet($item, $key);

            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    /**
     * Получить коллекцию толкьо с указаными ключами из текущих значений.
     *
     * @param mixed $keys
     * @return static
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Helper::arrayOnly($keys, $this->data));
    }

    /**
     * Получить и удалить последний элемент из текущей коллекции.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Добавить элемент в начало коллекции.
     *
     * @note вторым аргументом вы можете передать ключ добавляемого элемента
     *
     * @param mixed $value
     * @param mixed $key
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        $this->data = Helper::arrayPrepend($this->data, $value, $key);

        return $this;
    }

    /**
     * Получение и удаление элемента из коллекции.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Helper::arrayPull($key, $this->data, $default);
    }

    /**
     * Поместите элемент в коллекцию по ключу.
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
     * Вернет новый экземпляр коллекции с заполнеными значениями до тех пор, пока массив не достигнет указанного размера.
     *
     * @note Этот метод ведет себя как функция PHP массива array_pad.
     * @param int   $size
     * @param mixed $value
     * @return static
     */
    public function pad($size, $value)
    {
        return new static(array_pad($this->data, $size, $value));
    }

    /**
     * Получить один или несколько элементов случайным образом из коллекции.
     *
     * @link https://laravel.com/docs/5.6/collections#method-random
     * @param int $amount
     * @return static
     */
    public function random($amount = 1)
    {
        if ($amount > ($count = $this->count())) {
            $amount = $count;
        }

        if ($count === 0) {
            return new static([]);
        }

        $keys = array_rand($this->data, $amount);

        if ($amount == 1) {
            return new static([$this->data[$keys]]);
        }

        return new static(array_intersect_key($this->data, array_flip($keys)));
    }

    /**
     * Уменьшить коллекцию до одного значения.
     *
     * @param callable $callback
     * @param mixed    $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Создайте коллекцию всех элементов, которые не проходят данный тест истины.
     *
     * @param callable|mixed $callback
     * @return static
     */
    public function reject($callback)
    {
        if ($this->useAsCallable($callback)) {
            return $this->filter(function ($item) use ($callback) {
                return ! $callback($item);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }

    /**
     * Вернуть коллекцию в обратном порядоке элементов от базового.
     *
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->data));
    }

    /**
     * Найдите в коллекции заданное значение и в случае успеха вернёт соответствующий ключ.
     *
     * @param mixed $value
     * @param bool  $strict
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        if (! $this->useAsCallable($value)) {
            return array_search($value, $this->data, $strict);
        }

        foreach ($this->data as $key => $item) {
            if (call_user_func($value, $item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Получение и удалите первого элемент из коллекции.
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Вернуть коллекцию у которой все базовые элементы перемешаны.
     *
     * @return static
     */
    public function shuffle()
    {
        $items = $this->data;

        shuffle($items);

        return new static($items);
    }

    /**
     * Вернуть коллекцию по указаному базовому срезу элементов.
     *
     * @param int  $offset
     * @param int  $length
     * @param bool $preserveKeys
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->data, $offset, $length, $preserveKeys));
    }

    /**
     * Вернуть коллекцию в которой элементы отсортированы по возрастанию с помощью обратного вызова.
     *
     * @link https://laravel.com/docs/5.6/collections#method-sort
     * @param callable|null $callback
     * @return static
     */
    public function sort(callable $callback = null)
    {
        $items = $this->data;

        $callback
            ? uasort($items, $callback)
            : uasort($items, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return new static($items);
    }

    /**
     * Вернуть коллекцию которая отсортирована по заданному ключу.
     *
     * @note В отсортированной коллекции хранятся ключи исходного массива
     * Сортировка коллекции с помощью данного обратного вызова.
     *
     * @param callable|string $callback
     * @param int             $options
     * @param bool            $descending
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];

        $callback = $this->valueRetriever($callback);

        // Сначала мы будем контактировать элементы и получить компаратор из функции обратного вызова, которую нам дали.
        // Затем мы отсортируем возвращаемые значения и возьмем соответствующие значения для отсортированных ключей из этого массива.
        foreach ($this->data as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options) : asort($results, $options);

        // После того, как мы отсортировали все ключи в массиве,
        // мы пропустим их и возьмем соответствующую модель,
        // Затем мы просто вернем экземпляр коллекции.

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->data[$key];
        }

        return new static($results);
    }

    /**
     * Сортировка коллекции в порядке убывания с помощью данного обратного вызова.
     *
     * @param callable|string $callback
     * @param int             $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Возвращает коллекцию после среза элементов у базовой коллекции, начинающийся с указанного индекса
     *
     * @param int      $offset
     * @param int|null $length
     * @param mixed    $replacement
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() == 1) {
            return new static(array_splice($this->data, $offset));
        }

        return new static(array_splice($this->data, $offset, $length, $replacement));
    }

    /**
     * Получить сумму всех элементов в коллекции.
     *
     * @param callable|string|null $callback
     * @return mixed
     */
    public function sum($callback = null)
    {
        if (is_null($callback)) {
            return array_sum($this->data);
        }

        $callback = $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result += $callback($item);
        }, 0);
    }

    /**
     * Возвращает новую коллекцию с указанным количеством элементов
     *
     * @param integer $limit
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Преобразуйте каждый элемент в коллекцию с помощью обратного вызова.
     *
     * @note В отличие от других методов, transform изменяет саму коллекцию! Если вы хотите создать новую коллекцию, используйте метод map()
     * @param callable $callback
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->data = $this->map($callback)->all();

        return $this;
    }

    /**
     * Возвращает все уникальные элементы из базового массива в новой коллекции.
     *
     * @param string|callable|null $key
     * @return static
     */
    public function unique($key = null)
    {
        if (is_null($key)) {
            return new static(array_unique($this->data, SORT_REGULAR));
        }

        $key = $this->valueRetriever($key);

        $exists = [];

        return $this->reject(function ($item) use ($key, &$exists) {
            if (in_array($id = $key($item), $exists)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    /**
     * Возвращает новую коллекцию со сброшеными ключами к целым числам.
     *
     * @return static
     */
    public function values()
    {
        return new static(array_values($this->data));
    }

    /**
     * Получить значение, извлекающее обратный вызов.
     *
     * @param string $value
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return Helper::arrayGet($item, $value);
        };
    }

    // public function zip($items)

    /**
     * Результаты массива, элементов из коллекции или Arrayable
     *
     * @param mixed $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;

        } elseif ($items instanceof self) {
            return $items->all();

        } elseif ($items instanceof Arrayable) {
            return $items->toArray();

        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);

        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();

        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }
}