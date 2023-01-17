<?php

namespace Warkhosh\Component\Json;

use Exception;
use Throwable;

/**
 * Объект для декодирования JSON данных
 *
 * @version 1.1
 */
class JsonDecode
{
    /**
     * Строка JSON для декодирования
     *
     * @var mixed
     */
    private $json = null;

    /**
     * Декодированные данные из JSON строки
     *
     * @var mixed
     */
    private $value;

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
     * @param string $json
     * @param bool   $associative
     * @param int    $depth
     * @param int    $flags
     */
    public function __construct(string $json, ?bool $associative = true, int $depth = 512, int $flags = 0)
    {
        $this->json = trim($json);

        try {
            if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300 && defined('JSON_THROW_ON_ERROR')) {
                $flags += JSON_THROW_ON_ERROR;
            }

            $this->value = json_decode($this->json, $associative, $depth, $flags);

            // Фиксируем ошибку если передали не NULL а результат NULL
            if (trim($this->json) !== gettype(null) && $this->value === null) {
                throw new Exception("Error");
            }

        } catch (Throwable $e) {
            $this->hasError = false;
            $this->value = null;
        }
    }

    /**
     * Статичный вариант для начала работы с объектом
     *
     * @param string $json
     * @param bool   $associative
     * @param int    $depth
     * @param int    $flags
     * @return static
     */
    static public function init(string $json, ?bool $associative = true, int $depth = 512, int $flags = 0)
    {
        return new static($json, $associative, $depth, $flags);
    }

    /**
     * Установка флага для проверки сложных типов данных после декодирования
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
     * Быстрый синтаксис проверки удачного декодирования
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        if ($this->hasError === false) {
            return false;
        }

        // Если указан флаг декодирования данных только сложного типа (array | object)
        if ($this->simpleType === false) {
            $valueType = gettype($this->value); // Устанавливаем тип декодированных данных

            // Формируем ошибку если получился простой тип
            switch ($valueType) {
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
     * Быстрый синтаксис проверки не удачного декодирования
     *
     * @return bool
     */
    public function isFail(): bool
    {
        return ! $this->isSuccess();
    }

    /**
     * Бросает исключение если результат декодирования не удачный
     *
     * @param string|null $message
     * @return $this
     * @throws Exception
     */
    public function exceptionInError(?string $message = null)
    {
        if ($this->isFail() === true) {
            throw new Exception(empty($message) ? "JSON decoding error" : $message);
        }

        return $this;
    }

    /**
     * Возвращает декодированные данные из JSON
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Возвращает декодированные данные из JSON
     *
     * @alias getJson method
     * @return string
     */
    public function get()
    {
        return $this->getValue();
    }

    /**
     * Возвращает переданный для декодирования JSON
     *
     * @return mixed
     */
    public function getJson()
    {
        return $this->json;
    }
}