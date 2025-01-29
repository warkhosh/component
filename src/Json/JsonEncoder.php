<?php

namespace Warkhosh\Component\Json;

use Exception;
use Throwable;

/**
 * Объект для преобразования данных в JSON
 *
 * @version 1.1
 */
class JsonEncoder
{
    /**
     * Значения для преобразования в JSON строку
     *
     * @var string
     */
    private string $value = "";

    /**
     * Значения для преобразования
     *
     * @var mixed
     */
    private mixed $source;

    /**
     * Признак допущения простых типов (null, numeric, bool) для кодирования
     *
     * @var bool
     */
    private bool $simpleType = false;

    /**
     * @var bool
     */
    private bool $hasError = false;

    /**
     * @param mixed $value
     * @param int $flags
     * @param int $depth
     */
    public function __construct(mixed $value = null, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512)
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
            $this->hasError = false;
            $this->value = "{}";
        }
    }

    /**
     * Статичный вариант для начала работы с объектом
     *
     * @param mixed $value
     * @param int $flags
     * @param int $depth
     * @return static
     */
    public static function init(mixed $value = null, int $flags = 0, int $depth = 512): static
    {
        return new static($value, $flags, $depth);
    }

    /**
     * Установка флага для проверки полученных данных на сложный тип
     *
     * @param bool $flag
     * @return $this
     */
    public function simpleType(bool $flag): static
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
        if ($this->hasError === true) {
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
     * @param string|null $customMessage
     * @return $this
     * @throws Exception
     */
    public function exceptionInError(?string $customMessage = null): static
    {
        if ($this->isFail() === true) {
            $message = $this->simpleType === false ? "Specified a simple data type for JSON" : "JSON encoding error";
            throw new Exception(! empty($customMessage) ? $customMessage : $message);
        }

        return $this;
    }

    /**
     * Возвращает JSON
     *
     * @note вернёт пустоту если при кодировании возникла ошибка
     * @return string
     */
    public function getJson(): string
    {
        return $this->value;
    }

    /**
     * Возвращает JSON
     *
     * @alias getJson method
     * @return string
     */
    public function get(): string
    {
        return $this->getJson();
    }

    /**
     * Возвращает оригинальные данные что были указаны для кодирования
     *
     * @return mixed
     */
    public function getSource(): mixed
    {
        return $this->source;
    }
}
