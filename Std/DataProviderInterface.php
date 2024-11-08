<?php

namespace Warkhosh\Component\Std;

/**
 * Interface FacadeInterface
 *
 * @package Ekv\Framework\Components\Facade\Interfaces
 */
interface DataProviderInterface
{
    /**
     * Поддержка методов ArrayObject
     */

    /**
     * Сортировать записи по значению
     *
     * @link https://php.net/manual/en/arrayobject.asort.php
     * @param int $flags
     * @return $this
     */
    public function asort(int $flags = SORT_REGULAR): static;

    /**
     * Получить количество общедоступных свойств ArrayObject
     *
     * @link  https://php.net/manual/en/arrayobject.count.php
     * @return int
     */
    public function count(): int;

    /**
     * Сортировать записи по ключам
     *
     * @link https://php.net/manual/en/arrayobject.ksort.php
     * @param int $flags
     * @return $this
     */
    public function ksort(int $flags = SORT_REGULAR): static;

    /**
     * Сортировать массив, используя алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natsort.php
     * @return $this
     */
    public function natsort(): static;

    /**
     * Сортировать массив, используя регистронезависимый алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natcasesort.php
     * @return $this
     */
    public function natcasesort(): static;

    /**
     * Возвращает, существует ли указанный индекс
     *
     * @link https://php.net/manual/en/arrayobject.offsetexists.php
     * @param mixed $key
     * @return bool true if the requested index exists, otherwise false
     */
    public function offsetExists(mixed $key): bool;

    /**
     * Возвращает значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetget.php
     * @param mixed $key
     * @return mixed The value at the specified index or false.
     */
    public function offsetGet(mixed $key): mixed;

    /**
     * Устанавливает новое значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetset.php
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function offsetSet(mixed $key, mixed $value): static;

    /**
     * Удаляет значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetunset.php
     * @param mixed $key
     * @return $this
     */
    public function offsetUnset(mixed $key): static;

    /**
     * Генерирует пригодное для хранения представление
     *
     * @link https://php.net/manual/en/arrayobject.serialize.php
     * @return string
     */
    public function serialize(): string;

    /**
     * Сортировать записи, используя пользовательскую функцию для сравнения элементов и сохраняя при этом связь ключ/значение
     *
     * @link https://php.net/manual/en/arrayobject.uasort.php
     * @param callable $callback
     * @return $this
     */
    public function uasort(callable $callback): static;

    /**
     * Сортировать массив по ключам, используя пользовательскую функцию для сравнения
     *
     * @link https://php.net/manual/en/arrayobject.uksort.php
     * @param callable $callback
     * @return $this
     */
    public function uksort(callable $callback): static;

    /**
     * Добавляет значение в конец массива
     *
     * @link  https://php.net/manual/en/arrayobject.append.php
     * @param mixed $value
     * @return $this
     */
    public function append(mixed $value): static;

    /**
     * Устанавливает заданный ключ и значение
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function put(mixed $key, mixed $value): static;

    /**
     * Создаёт копию ArrayObject как массив
     *
     * @link  https://php.net/manual/en/arrayobject.getarraycopy.php
     * @return array
     */
    public function getArrayCopy(): array;

    /**
     * Получить список элементов в виде простого массива
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault(mixed $default): static;

    /**
     * @param bool $use
     * @return $this
     */
    public function setUseDefault(bool $use): static;

    /**
     * Возвращает результат проверки: не является ли пустой выборкой.
     *
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Возвращает результат проверки: является ли выборка пустой или нет.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Добавить указанные данные к существующим (перезапишет присутствующие значения)
     *
     * @param array $input
     * @return $this
     */
    public function merge(array $input = []): static;

    /**
     * Определяет, существует ли элемент в коллекции по ключу.
     *
     * @param float|int|string $key
     * @return bool
     */
    public function has(float|int|string $key): bool;

    /**
     * Получите коллекцию с ключами от предметов коллекции.
     *
     * @return static
     */
    public function keys(): static;

    /**
     * Удаление элемента по ключу.
     *
     * @param array|string $keys
     * @return $this
     */
    public function forget(array|string $keys): static;

    /**
     * Клонирование данных
     *
     * @return $this
     */
    public function clone(): static;

    /**
     * Преобразует все значения из многомерного в одно измерение
     *
     * @param int $depth
     * @return static
     */
    public function flatten(int $depth = INF): static;

    /**
     * Сворачивает коллекцию массивов в одну одномерную коллекцию
     *
     * @return static
     */
    public function collapse(): static;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * Выбрасывает показ всех значений и завершение сценария.
     *
     * @return void
     */
    public function dd(): void;

    /**
     * Преобразуйте значений в ее строковое JSON представление.
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string;

    /**
     * Добавление переданного исключения
     *
     * @param $exception
     * @return $this
     */
    public function addException($exception): static;
}
