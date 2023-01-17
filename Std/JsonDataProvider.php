<?php

namespace Warkhosh\Component\Std;

use Exception;

/**
 * Объект для работы с JSON
 *
 * @param string $value
 * @param mixed  $source
 */
class JsonDataProvider
{
    /**
     * Значения JSON строки
     *
     * @var string
     */
    public $value = "";

    /**
     * Кодируемые значения
     *
     * @var mixed
     */
    public $source = null;

    /**
     * @param array|string|int|float|null $input
     */
    public function __construct($input = [])
    {
        $this->source = $input;

        if (is_bool($this->source) || is_null($this->source) || is_numeric($this->source)) {
            $this->value = $input;
        }
    }

    /**
     * Кодирование данных в JSON
     *
     * @param array|string|int|float|null $value
     * @param int                         $flags
     * @param int                         $depth
     */
    public function encode($value, int $flags = 0, int $depth = 512)
    {
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
     * @return JsonDataProvider
     * @throws Exception
     */
    public function exceptionInError(?string $message = null)
    {
        if ($this->isFail() === true) {
            throw new Exception(empty($message) ? "JSON encoding error" : $message);
        }

        return $this;
    }
}