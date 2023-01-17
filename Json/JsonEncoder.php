<?php

namespace Warkhosh\Component\Json;

use Exception;
use Throwable;

/**
 * Объект для преобразования данных в JSON
 *
 * @param string $value
 * @param mixed  $source
 */
class JsonEncoder
{
    /**
     * Значения для преобразования в JSON строку
     *
     * @var string
     */
    private $value = "";

    /**
     * Значения для преобразования
     *
     * @var mixed
     */
    private $source;

    /**
     * Признак допущения простых типов (null, numeric, bool) для кодирования
     *
     * @var int
     */
    private $simpleType = false;

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @param mixed $value
     * @param int   $flags
     * @param int   $depth
     */
    public function __construct($value = null, int $flags = 0, int $depth = 512)
    {
        $this->source = $value;

        try {
            if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300 && defined('JSON_THROW_ON_ERROR')) {
                $flags += JSON_THROW_ON_ERROR;
            }

            $this->value = json_encode($value, $flags, $depth);

            // Фиксируем ошибку если передали не FALSE а результат FALSE
            if ($value !== false && $this->value === false) {
                throw new Exception("Error");
            }

        } catch (Throwable $e) {
            $this->value = $this->hasError = false;
        }
    }

    /**
     * Статичный вариант для начала работы с объектом
     *
     * @param null $value
     * @param int  $flags
     * @param int  $depth
     * @return static
     */
    static public function init($value = null, int $flags = 0, int $depth = 512)
    {
        return new static($value, $flags, $depth);
    }

    /**
     * Установка флага для проверки полученных данных на сложный тип
     *
     * @param bool $flag
     * @return $this
     */
    public function simpleType(bool $flag)
    {
        $this->simpleType = boolval($flag);

        return $this;
    }

    /**
     * Быстрый синтаксис проверки удачного преобразования
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        if ($this->hasError === false) {
            return false;
        }

        // Если указан флаг преобразования данных только сложного типа (array | object)
        if ($this->simpleType === false) {
            $varType = gettype($this->source); // Устанавливаем тип полученных данных

            // Формируем ошибку если тип был простой
            switch ($varType) {
                case "string":
                case "boolean":
                case "integer":
                case "double":
                case "NULL":
                    return false;
            }
        }

        return true;
    }

    /**
     * Быстрый синтаксис проверки не удачного преобразования
     *
     * @return bool
     */
    public function isFail(): bool
    {
        return ! $this->isSuccess();
    }

    /**
     * Бросает исключение если результат преобразования не удачный
     *
     * @param string|null $message
     * @return $this
     * @throws Exception
     */
    public function exceptionInError(?string $message = null)
    {
        if ($this->isFail() === true) {
            throw new Exception(empty($message) ? "JSON encoding error" : $message);
        }

        return $this;
    }

    /**
     * Возвращает JSON
     *
     * @note вернёт пустоту если при кодировании возникла ошибка
     * @return string
     */
    public function getJson()
    {
        return is_string($this->value) ? $this->value : "";
    }

    /**
     * Возвращает JSON
     *
     * @alias getJson method
     * @return string
     */
    public function get()
    {
        return $this->getJson();
    }

    /**
     * Возвращает оригинальные данные что были указаны для кодирования
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }
}