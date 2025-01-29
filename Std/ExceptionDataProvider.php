<?php

namespace Warkhosh\Component\Std;

use Exception;
use Throwable;

/**
 * ExceptionDataProvider
 *
 * Класс для своих исключений с дополнительными полями $field, $signal, $system
 */
class ExceptionDataProvider extends Exception implements Throwable
{
    /**
     * Название поля
     *
     * @var string|null
     */
    protected ?string $field = null;

    /**
     * signal
     *
     * @var int|string|null
     */
    protected int|string|null $signal = null;

    /**
     * Признак системной ошибки
     *
     * @note по этому флагу далее алгоритмы могут понимать что ошибка системная и дополнять техническими данными которые скрыты для других
     *
     * @var int
     */
    protected int $system = 1;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Static constructor
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return ExceptionDataProvider
     */
    public static function init(string $message = "", int $code = 0, Throwable $previous = null): ExceptionDataProvider
    {
        return new static($message, $code, $previous);
    }

    /**
     * @param string|null $field
     * @return $this
     */
    public function field(?string $field = null): static
    {
        $this->setField($field);

        return $this;
    }

    /**
     * @param string|null $field
     * @return void
     */
    private function setField(?string $field = null): void
    {
        $this->field = $field;
    }

    /**
     * @return string|null
     */
    public function getField(): string|null
    {
        return $this->field;
    }

    /**
     * @param int|string|null $signal
     * @return $this
     */
    public function signal(int|string|null $signal): static
    {
        $this->setSignal($signal);

        return $this;
    }

    /**
     * @param int|string|null $signal
     * @return void
     */
    private function setSignal(int|string|null $signal): void
    {
        $this->signal = $signal;
    }

    /**
     * @return int|string|null
     */
    public function getSignal(): int|string|null
    {
        return $this->signal;
    }

    /**
     * @param int $system
     * @return $this
     */
    public function system(int $system): static
    {
        $this->setSystem($system);

        return $this;
    }

    /**
     * @param int $system
     * @return void
     */
    private function setSystem(int $system): void
    {
        $this->system = $system;
    }

    /**
     * @return int
     */
    public function getSystem(): int
    {
        return $this->system;
    }
}
