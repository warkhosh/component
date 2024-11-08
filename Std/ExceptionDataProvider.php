<?php

namespace Warkhosh\Component\Std;

use Exception;
use Throwable;

class ExceptionDataProvider extends Exception
{
    /**
     * The error message
     *
     * @var string
     */
    protected $message;

    /**
     * The error code
     *
     * @var int
     */
    protected $code;

    /**
     * The filename where the error happened
     *
     * @var string
     */
    protected string $file;

    /**
     * The line where the error happened
     *
     * @var int
     */
    protected int $line;

    /**
     * Название поля
     *
     * @var string
     */
    protected string $field;

    /**
     * signal
     *
     * @var int|string|null
     */
    protected int|string|null $signal = null;

    /**
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
     * @param string $file
     * @return $this
     */
    public function file(string $file): static
    {
        $this->setFile($file);

        return $this;
    }

    /**
     * @param string $file
     * @return void
     */
    private function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @param string $line
     * @return $this
     */
    public function line(string $line): static
    {
        $this->setLine($line);

        return $this;
    }

    /**
     * @param string $line
     * @return void
     */
    private function setLine(string $line): void
    {
        $this->line = $line;
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
    public function signal($signal): static
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
