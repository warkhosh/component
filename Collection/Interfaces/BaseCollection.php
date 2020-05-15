<?php

namespace Ekv\Component\Collection\Interfaces;

/**
 * Interface BaseCollection
 *
 * Интерфейс коллекции
 *
 * @package Collection\Interfaces
 */
interface BaseCollection
{
    /**
     * Вернет новый экземпляр коллекции с указаными значениями.
     *
     * @param mixed $items
     * @return static
     */
    public static function make($items = []);

    /**
     * Вернет коллекцию с указаным значением, если это применимо.
     *
     * @param mixed $value
     * @return static
     */
    public static function wrap($value);

    /**
     * Получить базовые элементы из данной коллекции, если это применимо.
     *
     * @param array|static $value
     * @return array
     */
    public static function unwrap($value);

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault($default);

    /**
     * @return mixed
     *
     * public function getDefault(); */

    /**
     * Получить все элементы в коллекции.
     *
     * @return array
     */
    public function all();

    /**
     * Получить среднее значение элементов.
     *
     * @param string $key - ключ в значениях которых вычесляем среднее значение
     * @return mixed
     */
    public function avg($key = null);

    /**
     * Alias for the "avg" method.
     *
     * @param callable|string $callback
     * @return mixed
     */
    public function average($callback = null);

    /**
     * Получить медиану.
     *
     * @link https://en.wikipedia.org/wiki/Median
     * @param string $key - ключ в значениях которых вычесляем медиану
     * @return mixed
     */
    public function median($key = null);

    /**
     * Получить режим заданного ключа.
     *
     * @link https://laravel.com/docs/5.6/collections#method-mode
     * @link https://en.wikipedia.org/wiki/Mode_(statistics)
     * @param mixed $key
     * @return array|null
     */
    public function mode($key = null);

    /**
     * Дамп коллекции и завершение сценария.
     *
     * @return void
     */
    public function dd();

    /**
     * Выполнит обратный вызов по каждому элементу.
     *
     * @link https://laravel.com/docs/5.6/collections#method-each
     * @note Если вы хотите остановить итерацию через элементы, вы можете вернуть false из $callback
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback);

    /**
     * Метод для проверки того, что все элементы коллекции проходят заданный тест истинности
     *
     * @param string|callable $key
     * @param mixed           $operator
     * @param mixed           $value
     * @return bool
     */
    public function every($key, $operator = null, $value = null);

    /**
     * Вернет коллекцию с элементами кроме тех, у которых указаны указанные ключи.
     *
     * @param array|string $keys
     * @return static
     */
    public function except($keys);

    /**
     * Вернет коллекцию с элементами к которым был применен фильтр.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback = null);

    /**
     * Выполнит $callback функцию если указаное значение будет правда
     *
     * @link https://laravel.com/docs/5.6/collections#method-when
     * @param bool     $value
     * @param callable $callback
     * @param callable $default
     * @return mixed
     */
    public function when($value, callable $callback, callable $default = null);

    /**
     * Выполнит $callback функцию если указаное значение будет ложь.
     *
     * @link https://laravel.com/docs/5.6/collections#method-unless
     * @param bool     $value
     * @param callable $callback
     * @param callable $default
     * @return mixed
     */
    public function unless($value, callable $callback, callable $default = null);

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
    public function where($key, $operator, $value = null);

    /**
     * Возращает первый элемент в коллекции.
     *
     * @param callable $callback
     * @param mixed    $default
     * @return mixed
     */
    public function first(callable $callback = null, $default = null);

    /**
     * Возвращает коллекцию в которой многомерная коллекцию сплющенный в одно измерение.
     *
     * @link https://laravel.com/docs/5.6/collections#method-flatten
     * @param int $depth
     * @return static
     */
    public function flatten($depth = INF);

    /**
     * Возвращает коллекцию в которой исходные значения перевернуты наоборот
     *
     * @return static
     */
    public function flip();

    /**
     * Удаление элемента из коллекции по ключу.
     *
     * @param string|array $keys
     * @return $this
     */
    public function forget($keys);

    /**
     * Возвращает коллекцию сгруппированную по указанному ключу с использованием обратного вызова.
     *
     * @link https://laravel.com/docs/5.6/collections#method-groupby
     * @param callable|string $groupBy
     * @param bool            $preserveKeys - признак созранения ключей
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false);

    /**
     * Возвращает коллекцию сгруппированную по указанному ключу
     *
     * @link https://laravel.com/docs/5.6/collections#method-keyby
     * @note Если несколько элементов имеют один и тот же ключ, в новой коллекции будет отображаться только последний.
     * @param callable|string $keyBy
     * @return static
     */
    public function keyBy($keyBy);

    /**
     * Определяет, существует ли элемент в коллекции по ключу.
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key);

    /**
     * Concatenate values of a given key as a string.
     *
     * @param string $value
     * @param string $glue
     * @return string
     */
    public function implode($value, $glue = null);

    /**
     * Удаляет все значения из исходной коллекции, отсутствующие в данном массиве или коллекции.
     *
     * @note Полученная коллекция сохранит ключи исходной коллекции
     * @param mixed $items
     * @return static
     */
    public function intersect($items);

    /**
     * Возращает результат проверки: не является ли коллекция пустой.
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Возращает результат проверки: является ли коллекция пустой или нет.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Получите колекцию с ключами от предметов коллекции.
     *
     * @return static
     */
    public function keys();

    /**
     * Возвращает последний элемент в массиве.
     *
     * @link https://laravel.com/docs/5.6/collections#method-last
     * @note Если указан $callback вернет последний элемент в коллекции, который проходит данный тест истины
     * @param callable $callback
     * @param mixed    $default
     * @return mixed
     */
    public function last(callable $callback = null, $default = null);

    /**
     * Получить колекцию со значениями из массива текущей коллекции.
     *
     * @note можно указать конкретный ключь в массиве для использования в новом массиве
     * @link https://laravel.com/docs/5.6/collections#method-pluck
     * @param string      $value
     * @param string|null $key
     * @return static
     */
    public function pluck($value, $key = null);


    /**
     * Вернуть коллекцию после итерацию над каждым элементом в ней.
     *
     * @note Метод map выполняет итерацию по коллекции и передает каждое значение заданному обратному вызову.
     * Обратный вызов может изменять элемент и возвращать его, тем самым формируя новую коллекцию измененных элементов
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback);

    /**
     * Получить максимальное значение данного ключа.
     *
     * @note без ключа делает поиск по всему массиву
     * @link https://laravel.com/docs/5.6/collections#method-max
     * @param string|null $key
     * @return mixed
     */
    public function max($key = null);

    /**
     * Вернуть коллекцию после обхединения с заданными элементами.
     *
     * @param mixed $items
     * @return static
     */
    public function merge($items);

    /**
     * Получить минимальное значение по указаному ключу.
     *
     * @note без ключа делает поиск по всему массиву
     * @link https://laravel.com/docs/5.6/collections#method-min
     * @param string|null $key
     * @return mixed
     */
    public function min($key = null);

    /**
     * Получить коллекцию толкьо с указаными ключами из текущих значений.
     *
     * @param mixed $keys
     * @return static
     */
    public function only($keys);

    /**
     * Получить и удалить последний элемент из текущей коллекции.
     *
     * @return mixed
     */
    public function pop();

    /**
     * Добавить элемент в начало коллекции.
     *
     * @param mixed $value
     * @param mixed $key
     * @return $this
     */
    public function prepend($value, $key = null);

    /**
     * Добавить элемент в конец коллекции.
     *
     * @param mixed $value
     * @return $this
     */
    public function push($value);

    /**
     * Получение и удаление элемента из коллекции.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Поместите элемент в коллекцию по ключу.
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function put($key, $value);

    /**
     * Вернет новый экземпляр коллекции с заполнеными значениями до тех пор, пока массив не достигнет указанного размера.
     *
     * @note Этот метод ведет себя как функция PHP массива array_pad.
     * @param int   $size
     * @param mixed $value
     * @return static
     */
    public function pad($size, $value);

    /**
     * Получить один или несколько элементов случайным образом из коллекции.
     *
     * @param int $amount
     * @return static
     *
     * @link https://laravel.com/docs/5.6/collections#method-random
     */
    public function random($amount = 1);

    /**
     * Уменьшить коллекцию до одного значения.
     *
     * @param callable $callback
     * @param mixed    $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null);

    /**
     * Создайте коллекцию всех элементов, которые не проходят данный тест истины.
     *
     * @param callable|mixed $callback
     * @return static
     */
    public function reject($callback);

    /**
     * Вернуть коллекцию в обратном порядоке элементов от базового.
     *
     * @return static
     */
    public function reverse();

    /**
     * Найдите в коллекции заданное значение и в случае успеха вернёт соответствующий ключ.
     *
     * @param mixed $value
     * @param bool  $strict
     * @return mixed
     */
    public function search($value, $strict = false);

    /**
     * Получение и удалите первого элемент из коллекции.
     *
     * @return mixed
     */
    public function shift();

    /**
     * Вернуть коллекцию у которой все базовые элементы перемешаны.
     *
     * @return static
     */
    public function shuffle();

    /**
     * Вернуть коллекцию по указаному базовому срезу элементов.
     *
     * @param int  $offset
     * @param int  $length
     * @param bool $preserveKeys
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false);

    /**
     * Вернуть коллекцию в которой элементы отсортированы по возрастанию с помощью обратного вызова.
     *
     * @link https://laravel.com/docs/5.6/collections#method-sort
     * @param callable|null $callback
     * @return static
     */
    public function sort(callable $callback = null);

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
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false);

    /**
     * Сортировка коллекции в порядке убывания с помощью данного обратного вызова.
     *
     * @param callable|string $callback
     * @param int             $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR);

    /**
     * Возвращает коллекцию после среза элементов у базовой коллекции, начинающийся с указанного индекса
     *
     * @param int      $offset
     * @param int|null $length
     * @param mixed    $replacement
     * @return static
     */
    public function splice($offset, $length = null, $replacement = []);

    /**
     * Получить сумму всех элементов в коллекции.
     *
     * @param callable|string|null $callback
     * @return mixed
     */
    public function sum($callback = null);

    /**
     * Возвращает новую коллекцию с указанным количеством элементов
     *
     * @param integer $limit
     * @return static
     */
    public function take($limit);

    /**
     * Преобразуйте каждый элемент в коллекцию с помощью обратного вызова.
     *
     * @note В отличие от других методов, transform изменяет саму коллекцию! Если вы хотите создать новую коллекцию, используйте метод map()
     * @param callable $callback
     * @return $this
     */
    public function transform(callable $callback);

    /**
     * Возвращает все уникальные элементы из базового массива в новой коллекции.
     *
     * @param string|callable|null $key
     * @return static
     */
    public function unique($key = null);

    /**
     * Возвращает новую коллекцию со сброшеными ключами к целым числам.
     *
     * @return static
     */
    public function values();
}