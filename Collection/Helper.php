<?php

namespace Warkhosh\Component\Collection;

use ArrayAccess;
use Closure;

class Helper
{
    /**
     * Фильтруйте массив, используя данный обратный вызов.
     *
     * @param array    $array
     * @param callable $callback
     * @return array
     */
    public static function getWhere($array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Возвращает все элементы, кроме тех, чьи ключи указаны в передаваемом массиве
     *
     * @note в удаляемых ключах допускается точка для вложенного действия
     *
     * @param array | string $keys  - ключи которые надо исключить
     * @param array          $array - массив в котором убираем значения по ключам
     * @return array
     */
    static public function getExcept($keys = [], $array = []): array
    {
        static::arrayForget($keys, $array);

        return $array;
    }

    /**
     * Удаляет в массиве один или несколько ключей из переданных значений.
     *
     * @note в удаляемых ключах допускается точка для вложенного действия
     * @note улучшенный вариант without() но взят из laravel и нужно переписать!
     *
     * @param array|string $keys  - ключи которые надо исключить
     * @param array        $array - массив в котором убираем значения по ключам
     * @return void
     */
    public static function arrayForget($keys, &$array): void
    {
        $original = &$array;
        $keys = (array)$keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // если точный ключ существует на верхнем уровне, удалите его
            if (static::exists($key, $array)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // очищать перед каждым проходом
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Проверяет, существует ли данный ключ в предоставленном массиве.
     *
     * @param string|int        $key
     * @param ArrayAccess|array $array
     * @return bool
     */
    public static function exists($key, $array): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Получить элемент из массива или объекта с использованием нотации "точка".
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param mixed        $default
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public static function arrayGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $segment) {
            if (is_array($target)) {
                if (! array_key_exists($segment, $target)) {
                    return $default instanceof Closure ? $default() : $default;
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (! isset($target[$segment])) {
                    return $default instanceof Closure ? $default() : $default;
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (! isset($target->{$segment})) {
                    return $default instanceof Closure ? $default() : $default;
                }

                $target = $target->{$segment};
            } else {
                return $default instanceof Closure ? $default() : $default;
            }
        }

        return $target;
    }

    /**
     * Возвращает первый элемента массива, прошедшего заданный тест истинности.
     *
     * @note Вы также можете вызвать метод без аргументов, чтобы получить первый элемент в списке.
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public static function arrayFirst($array = [], callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default instanceof Closure ? $default() : $default;
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return $default instanceof Closure ? $default() : $default;
    }

    /**
     * Возвращает последний элемент в массиве, прошедшего заданный тест истинности
     *
     * @note можно вызвать последний метод без аргументов, чтобы получить последний элемент в коллекции
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public static function arrayLast($array = [], callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? ($default instanceof Closure ? $default() : $default) : end($array);
        }

        return static::arrayFirst(array_reverse($array), $callback, $default);
    }

    /**
     * Сгладьте многомерный массив на один уровень.
     *
     * @param array $array
     * @param int   $depth
     * @return array
     */
    public static function arrayFlatten($array = [], $depth = INF): array
    {
        if (! is_array($array)) {
            return [];
        }

        return array_reduce($array, function ($result, $item) use ($depth) {
            $item = $item instanceof \Warkhosh\Component\Collection\Interfaces\BaseCollection ? $item->all() : $item;

            if (! is_array($item)) {
                return array_merge($result, [$item]);

            } elseif ($depth === 1) {
                return array_merge($result, array_values($item));

            } else {
                return array_merge($result, static::arrayFlatten($item, $depth - 1));
            }
        }, []);
    }

    /**
     * Извлеките массив значений из массива.
     *
     * @param array             $array
     * @param string|array      $value
     * @param string|array|null $key
     * @return array
     */
    public static function arrayPluck($array, $value, $key = null): array
    {
        $results = [];

        [$value, $key] = static::arrayExplodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = static::arrayGet($item, $value);

            // Если ключ "null", мы просто добавим значение в массив и продолжим цикл.
            // В противном случае мы будем использовать массив, используя значение ключа, полученного нами от разработчика.
            // Затем мы вернем форму окончательного массива.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = static::arrayGet($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Взорвите аргументы "value" и "key", переданные static::getPluck().
     *
     * @param string|array      $value
     * @param string|array|null $key
     * @return array
     */
    protected static function arrayExplodePluckParameters($value, $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Оставить подмножество элементов из заданного массива.
     *
     * @param array | string $haystack - список с допустимых значений
     * @param array          $array    - список который фильтруем
     * @return array
     */
    public static function arrayOnly($haystack, array $array): array
    {
        return array_intersect_key($array, array_flip((array)$haystack));
    }

    /**
     * Добавить элемент в начало массива.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     * @return array
     */
    public static function arrayPrepend($array, $value, $key = null): array
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Получить значение из массива по ключу и удаление этого значения.
     *
     * @param string|array $key
     * @param array        $array
     * @param mixed        $default
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public static function arrayPull($key, &$array, $default = null)
    {
        if (is_array($key)) {
            $value = [];

            foreach ($key as $keyStr) {
                $value[] = static::arrayPull((string)$keyStr, $array, $default);
                static::arrayExcept((string)$keyStr, $array);
            }

            return count($value) > 0 ? $value : (array)$default;

        }

        $value = static::arrayGet((string)$key, $array, $default);
        static::arrayExcept((string)$key, $array);

        return $value;
    }

    /**
     * Удаляет в массиве один или несколько ключей из переданных значений
     *
     * @note в удаляемых ключах допускается точка для вложенного действия
     *
     * @param array|string $keys  - ключи которые надо исключить
     * @param array        $array - массив в котором убираем значения по ключам
     * @return void
     */
    static public function arrayExcept($keys, &$array): void
    {
        static::arrayForget($keys, $array);
    }
}
