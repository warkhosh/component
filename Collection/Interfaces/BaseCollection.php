<?php

namespace Warkhosh\Component\Collection\Interfaces;

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
     * Вернет новый экземпляр коллекции с указанными значениями
     *
     * @param mixed $items
     * @return static
     */
    public static function make(mixed $items = []): static;

    /**
     * Вернет коллекцию с указанным значением, если это применимо
     *
     * @param mixed $value
     * @return static
     */
    public static function wrap(mixed $value): static;

    /**
     * Получить базовые элементы из данной коллекции, если это применимо
     *
     * @param array|BaseCollection $value
     * @return array
     */
    public static function unwrap(array|BaseCollection $value): array;

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault(mixed $default): static;

    /**
     * @return mixed
     *
     * public function getDefault(); */

    /**
     * Получить все элементы в коллекции
     *
     * @return array
     */
    public function all(): array;

    /**
     * Получить среднее значение элементов
     *
     * @param callable|string|null $key ключ в значениях которых вычисляем среднее значение
     * @return float|int
     */
    public function avg(callable|string|null $key = null): float|int;

    /**
     * Alias for the "avg" method
     *
     * @param callable|string|null $callback
     * @return float|int
     */
    public function average(callable|string|null $callback = null): float|int;

    /**
     * Получить медиану.
     *
     * @link https://en.wikipedia.org/wiki/Median
     *
     * @param array|string|null $key ключ в значениях которых вычисляем медиану
     * @return float|int|null
     */
    public function median(array|string|null $key = null): float|int|null;

    /**
     * Получить режим заданного ключа
     *
     * @link https://laravel.com/docs/5.6/collections#method-mode
     * @link https://en.wikipedia.org/wiki/Mode_(statistics)
     *
     * @param array|string|null $key
     * @return array|null
     */
    public function mode(array|string|null $key = null): array|null;

    /**
     * Дамп коллекции и завершение сценария
     *
     * @return void
     */
    public function dd(): void;

    /**
     * Выполнит обратный вызов по каждому элементу
     *
     * @link https://laravel.com/docs/5.6/collections#method-each
     * @note Если вы хотите остановить итерацию через элементы, вы можете вернуть false из $callback
     *
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback): static;

    /**
     * Метод для проверки того, что все элементы коллекции проходят заданный тест истинности
     *
     * @param callable|string $key
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    public function every(callable|string $key, mixed $operator = null, mixed $value = null): bool;

    /**
     * Вернет коллекцию с элементами кроме тех, у которых указаны указанные ключи
     *
     * @param array|string $keys
     * @return static
     */
    public function except(array|string $keys): static;

    /**
     * Вернет коллекцию с элементами к которым был применен фильтр
     *
     * @param callable|null $callback
     * @return static
     */
    public function filter(?callable $callback = null): static;

    /**
     * Выполнит $callback функцию если указанное значение будет правда
     *
     * @link https://laravel.com/docs/5.6/collections#method-when
     * @note функция $callback должна вернуть эту-же коллекцию
     *
     * @param bool $value
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function when(bool $value, callable $callback, callable $default = null): static;

    /**
     * Выполнит $callback функцию если указанное значение будет ложь
     *
     * @link https://laravel.com/docs/5.6/collections#method-unless
     * @note функция $callback должна вернуть эту-же коллекцию
     *
     * @param bool $value
     * @param callable $callback
     * @param callable|null $default
     * @return mixed
     */
    public function unless(bool $value, callable $callback, callable $default = null): static;

    /**
     * Возвращает новую коллекцию после фильтрации элементов по заданной паре значений
     *
     * @link https://laravel.com/docs/5.6/collections#method-where
     * @note Метод использует «свободные» сравнения при проверке значений элементов.
     *       Строка с целым значением будет считаться равной целому числу того же значения.
     *       Используйте метод whereStrict для фильтрации с использованием «строгих» сравнений.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return static
     */
    public function where(string $key, mixed $operator, mixed $value = null): static;

    /**
     * Возвращает первый элемент в коллекции
     *
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public function first(callable $callback = null, mixed $default = null): mixed;

    /**
     * Возвращает коллекцию в которой многомерная коллекция сплющено в одно измерение
     *
     * @link https://laravel.com/docs/5.6/collections#method-flatten
     *
     * @param int $depth
     * @return static
     */
    public function flatten(int $depth = INF): static;

    /**
     * Возвращает коллекцию в которой исходные значения перевернуты наоборот
     *
     * @return static
     */
    public function flip(): static;

    /**
     * Удаление элемента из коллекции по ключу.
     *
     * @param mixed $keys
     * @return $this
     */
    public function forget(mixed $keys): static;

    /**
     * Возвращает коллекцию, сгруппированную по указанному ключу с использованием обратного вызова
     *
     * @link https://laravel.com/docs/5.6/collections#method-groupby
     *
     * @param callable|string $groupBy
     * @param bool $preserveKeys - признак сохранения ключей
     * @return static
     */
    public function groupBy(callable|string $groupBy, bool $preserveKeys = false): static;

    /**
     * Возвращает коллекцию, сгруппированную по указанному ключу
     *
     * @link https://laravel.com/docs/5.6/collections#method-keyby
     * @note Если несколько элементов имеют один и тот же ключ, в новой коллекции будет отображаться только последний
     *
     * @param callable|string $keyBy
     * @return static
     */
    public function keyBy(callable|string $keyBy): static;

    /**
     * Определяет, существует ли элемент(ы) в коллекции по ключу
     *
     * @note если передан список ключей, то система при первой неудачной проверке вернет false
     *
     * @param array|float|int|string $key
     * @return bool
     */
    public function has(array|float|int|string $key): bool;

    /**
     * Соединяет элементы в коллекции
     *
     * @param string $value
     * @param string|null $glue
     * @return string
     */
    public function implode(string $value, string $glue = null): string;

    /**
     * Удаляет все значения из исходной коллекции, отсутствующие в данном массиве или коллекции
     *
     * @note Полученная коллекция сохранит ключи исходной коллекции
     *
     * @param mixed $items
     * @return static
     */
    public function intersect(mixed $items): static;

    /**
     * Возвращает результат проверки: не является ли коллекция пустой
     *
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Возвращает результат проверки: является ли коллекция пустой или нет
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Получите коллекцию с ключами от предметов коллекции
     *
     * @return static
     */
    public function keys(): static;

    /**
     * Возвращает последний элемент в массиве
     *
     * @link https://laravel.com/docs/5.6/collections#method-last
     * @note Если указан $callback вернет последний элемент в коллекции, который проходит данный тест истины
     *
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public function last(callable $callback = null, mixed $default = null): mixed;

    /**
     * Получить коллекцию со значениями из массива текущей коллекции
     *
     * @note можно указать конкретный ключ в массиве для использования в новом массиве
     * @link https://laravel.com/docs/5.6/collections#method-pluck
     *
     * @param string $value
     * @param string|null $key
     * @return static
     */
    public function pluck(string $value, ?string $key = null): static;

    /**
     * Вернуть коллекцию после итерацию над каждым элементом в ней
     *
     * @note Метод map выполняет итерацию по коллекции и передает каждое значение заданному обратному вызову.
     *       Обратный вызов может изменять элемент и возвращать его, тем самым формируя новую коллекцию измененных элементов.
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static;

    /**
     * Получить максимальное значение данного ключа
     *
     * @link https://laravel.com/docs/5.6/collections#method-max
     * @note без ключа делает поиск по всему массиву
     *
     * @param string|null $key
     * @return float|int
     */
    public function max(?string $key = null): float|int;

    /**
     * Вернуть коллекцию после объединения с заданными элементами
     *
     * @param mixed $items
     * @return static
     */
    public function merge(mixed $items): static;

    /**
     * Получить минимальное значение по указанному ключу
     *
     * @note без ключа делает поиск по всему массиву
     * @link https://laravel.com/docs/5.6/collections#method-min
     *
     * @param string|null $key
     * @return float|int
     */
    public function min(?string $key = null): float|int;

    /**
     * Получить коллекцию толкло с указанными ключами из текущих значений
     *
     * @param array $keys
     * @return static
     */
    public function only(array $keys): static;

    /**
     * Получить и удалить последний элемент из текущей коллекции
     *
     * @return mixed
     */
    public function pop(): mixed;

    /**
     * Добавить элемент в начало коллекции
     *
     * @param mixed $value
     * @param mixed $key
     * @return $this
     */
    public function prepend(mixed $value, mixed $key = null): static;

    /**
     * Добавить элемент в конец коллекции
     *
     * @param mixed $value
     * @return $this
     */
    public function push(mixed $value): static;

    /**
     * Получение и удаление элемента из коллекции
     *
     * @param array|float|int|string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull(array|float|int|string $key, mixed $default = null): mixed;

    /**
     * Поместите элемент в коллекцию по ключу
     *
     * @param float|int|string $key
     * @param mixed $value
     * @return $this
     */
    public function put(float|int|string $key, mixed $value): static;

    /**
     * Вернет новый экземпляр коллекции с заполненными значениями до тех пор, пока массив не достигнет указанного размера
     *
     * @note Этот метод ведет себя как функция PHP массива array_pad
     *
     * @param int $size
     * @param mixed $value
     * @return static
     */
    public function pad(int $size, mixed $value): static;

    /**
     * Получить один или несколько элементов случайным образом из коллекции
     *
     * @link https://laravel.com/docs/5.6/collections#method-random
     *
     * @param int $amount
     * @return static
     */
    public function random(int $amount = 1): static;

    /**
     * Уменьшить коллекцию до одного значения.
     *
     * @param callable $callback
     * @param mixed $initial
     * @return float|int
     */
    public function reduce(callable $callback, ?int $initial = null): float|int;

    /**
     * Фильтрует коллекцию, используя заданную функцию обратного вызова
     *
     * @note функция обратного вызова должна возвращать true для элементов, которые необходимо удалить из результирующей коллекции
     *
     * @param callable|float|int|string $callback
     * @param bool $strict
     * @return static
     */
    public function reject(callable|float|int|string $callback, bool $strict = false): static;

    /**
     * Вернуть коллекцию в обратном порядке элементов от базового
     *
     * @return static
     */
    public function reverse(): static;

    /**
     * Найдите в коллекции заданное значение и в случае успеха вернёт соответствующий ключ
     *
     * @param mixed $value
     * @param bool $strict
     * @return false|int|string
     */
    public function search(mixed $value, bool $strict = false): false|int|string;

    /**
     * Получение и удалите первого элемент из коллекции
     *
     * @return mixed
     */
    public function shift(): mixed;

    /**
     * Вернуть коллекцию у которой все базовые элементы перемешаны
     *
     * @return static
     */
    public function shuffle(): static;

    /**
     * Вернуть коллекцию по указанному базовому срезу элементов
     *
     * @param int $offset
     * @param int|null $length
     * @param bool $preserveKeys
     * @return static
     */
    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false): static;

    /**
     * Вернуть коллекцию в которой элементы отсортированы по возрастанию с помощью обратного вызова
     *
     * @link https://laravel.com/docs/5.6/collections#method-sort
     *
     * @param callable|null $callback
     * @return static
     */
    public function sort(callable $callback = null): static;

    /**
     * Вернуть коллекцию которая отсортирована по заданному ключу
     *
     * @note В отсортированной коллекции хранятся ключи исходного массива.
     *       Сортировка коллекции с помощью данного обратного вызова.
     *
     * @param callable|string $callback
     * @param int $options
     * @param bool $descending
     * @return static
     */
    public function sortBy(callable|string $callback, int $options = SORT_REGULAR, bool $descending = false): static;

    /**
     * Сортировка коллекции в порядке убывания с помощью данного обратного вызова
     *
     * @param callable|string $callback
     * @param int $options
     * @return static
     */
    public function sortByDesc(callable|string $callback, int $options = SORT_REGULAR): static;

    /**
     * Возвращает коллекцию после среза элементов у базовой коллекции, начинающийся с указанного индекса
     *
     * @param int $offset
     * @param int|null $length
     * @param mixed $replacement
     * @return static
     */
    public function splice(int $offset, ?int $length = null, mixed $replacement = []): static;

    /**
     * Получить сумму всех элементов в коллекции.
     *
     * @link https://laravel.ru/docs/v5/collections#sum
     *
     * @param callable|string|null $callback
     * @return float|int
     */
    public function sum(callable|string|null $callback = null): float|int;

    /**
     * Возвращает новую коллекцию с указанным количеством элементов
     *
     * @param int $limit
     * @return static
     */
    public function take(int $limit): static;

    /**
     * Преобразуйте каждый элемент в коллекцию с помощью обратного вызова
     *
     * @note В отличие от других методов, transform изменяет саму коллекцию! Если вы хотите создать новую коллекцию, используйте метод map()
     *
     * @param callable $callback
     * @return $this
     */
    public function transform(callable $callback): static;

    /**
     * Возвращает все уникальные элементы из базового массива в новой коллекции
     *
     * @param callable|string|null $key
     * @return static
     */
    public function unique(callable|string|null $key = null): static;

    /**
     * Возвращает новую коллекцию со сброшенными ключами к целым числам
     *
     * @return static
     */
    public function values(): static;
}
