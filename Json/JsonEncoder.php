<?php

namespace Warkhosh\Component\Json;

use Exception;

/**
 * Объект для работы с JSON
 *
 * @param string $value
 * @param mixed  $source
 */
class JsonEncoder
{
    /**
     * Значения JSON строки
     *
     * @var string
     */
    private $value = "";

    /**
     * Кодируемые значения
     *
     * @var mixed
     */
    private $source = null;

    /**
     * @param mixed $value
     * @param int   $flags
     * @param int   $depth
     */
    public function __construct($value = null, int $flags = 0, int $depth = 512)
    {
        $this->source = $value;
        $this->value = json_encode($value, $flags, $depth);
    }

    /**
     * Быстрый синтаксис проверки удачного преобразования
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        if (is_bool($this->source) || is_null($this->source) || is_numeric($this->source)) {
            return true;

        } elseif (! empty($this->source) && ! empty($this->value)) {
            return true;
        }

        return false;
    }

    /**
     * Быстрый синтаксис проверки состояния
     *
     * @return bool
     */
    public function isFail(): bool
    {
        if (is_bool($this->source) || is_null($this->source) || is_numeric($this->source)) {
            return false;

        } elseif (! empty($this->source) && empty($this->value)) {
            return true;
        }

        return false;
    }

    /**
     * Бросает исключение если объект имеет значение отрицательного результата
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
     * Возвращает оригинальные данные что были указаны для кодирования
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }
}