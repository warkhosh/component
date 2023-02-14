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
    public function asort($flags = SORT_REGULAR);

    /**
     * Получить количество общедоступных свойств ArrayObject
     *
     * @link  https://php.net/manual/en/arrayobject.count.php
     * @return int
     */
    public function count();

    /**
     * Сортировать записи по ключам
     *
     * @link https://php.net/manual/en/arrayobject.ksort.php
     * @param int $flags
     * @return $this
     */
    public function ksort($flags = SORT_REGULAR);

    /**
     * Сортировать массив, используя алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natsort.php
     * @return $this
     */
    public function natsort();

    /**
     * Сортировать массив, используя регистронезависимый алгоритм "natural order"
     *
     * @link  https://php.net/manual/en/arrayobject.natcasesort.php
     * @return $this
     */
    public function natcasesort();

    /**
     * Возвращает, существует ли указанный индекс
     *
     * @link https://php.net/manual/en/arrayobject.offsetexists.php
     * @param mixed $index
     * @return bool true if the requested index exists, otherwise false
     */
    public function offsetExists($index);

    /**
     * Возвращает значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetget.php
     * @param mixed $index
     * @return mixed The value at the specified index or false.
     */
    public function offsetGet($index);

    /**
     * Устанавливает новое значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetset.php
     * @param mixed $index
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($index, $value);

    /**
     * Удаляет значение по указанному индексу
     *
     * @link https://php.net/manual/en/arrayobject.offsetunset.php
     * @param integer | string $index
     * @return $this
     */
    public function offsetUnset($index);

    /**
     * Генерирует пригодное для хранения представление
     *
     * @link https://php.net/manual/en/arrayobject.serialize.php
     * @return string
     */
    public function serialize();

    /**
     * Сортировать записи, используя пользовательскую функцию для сравнения элементов и сохраняя при этом связь ключ/значение
     *
     * @link https://php.net/manual/en/arrayobject.uasort.php
     * @param callable $function
     * @return $this
     */
    public function uasort($function);

    /**
     * Сортировать массив по ключам, используя пользовательскую функцию для сравнения
     *
     * @link https://php.net/manual/en/arrayobject.uksort.php
     * @param callable $function
     * @return $this
     */
    public function uksort($function);

    /**
     * Добавляет значение в конец массива
     *
     * @link  https://php.net/manual/en/arrayobject.append.php
     * @param mixed $value
     * @return $this
     */
    public function append($value);

    /**
     * Устанавливает заданный ключ и значение
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function put($key, $value);

    /**
     * Создаёт копию ArrayObject как массив
     *
     * @link  https://php.net/manual/en/arrayobject.getarraycopy.php
     * @return array
     */
    public function getArrayCopy();

    /**
     * Получить список элементов в виде простого массива
     *
     * @return array
     */
    public function toArray();

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault($default);

    /**
     * @param boolean $use
     * @return $this
     */
    public function setUseDefault($use);

    /**
     * Возвращает результат проверки: не является ли пустой выборкой.
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Возвращает результат проверки: является ли выборка пустой или нет.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Добавить указанные данные к существующим ( перезапишет присутствующие значения )
     *
     * @param array $input
     * @return $this
     */
    public function merge($input = []);

    /**
     * Определяет, существует ли элемент в коллекции по ключу.
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key);

    /**
     * Получите колекцию с ключами от предметов коллекции.
     *
     * @return static
     */
    public function keys();
    /**
     * Удаление элемента по ключу.
     *
     * @param string|array $keys
     * @return $this
     */
    public function forget($keys);

    /**
     * Клонирование данных
     *
     * @return $this
     */
    public function clone();

    /**
     * Преобразует все значения из многомерного в одно измерение
     *
     * @param int $depth
     * @return static
     */
    public function flatten($depth = INF);

    /**
     * Сворачивает коллекцию массивов в одну одномерную коллекцию
     *
     * @return static
     */
    public function collapse();

    /**
     * @return array
     */
    public function all();

    /**
     * Выбрасывает показ всех значений и завершение сценария.
     *
     * @return void
     */
    public function dd();

    /**
     * Преобразуйте значений в ее строковое JSON представление.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0);

    /**
     * Добавление переданного исключения
     *
     * @param $exception
     * @return $this
     */
    public function addException($exception);
}