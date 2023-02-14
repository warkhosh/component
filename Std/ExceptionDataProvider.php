<?php

namespace Warkhosh\Component\Std;

class ExceptionDataProvider extends \Exception
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
    protected $file;

    /**
     * The line where the error happened
     *
     * @var string
     */
    protected $line;

    /**
     * Название поля
     *
     * @var string
     */
    protected $field;

    /**
     * signal
     *
     * @var int|string|null
     */
    protected $signal = null;

    /**
     * @var int
     */
    protected $system = 1;

    public function __construct(string $message = "", int $code = 1, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Static constructor
     *
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     * @return \Warkhosh\Component\Std\ExceptionDataProvider
     */
    #[\ReturnTypeWillChange]
    public static function init($message = "", $code = 0, \Throwable $previous = null) {
        return new static($message, $code, $previous);
    }

    /**
     * @param string $file
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function file(
        string $file
    ) {
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
    #[\ReturnTypeWillChange]
    public function line(
        string $line
    ) {
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
     * @param null|string $field
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function field(
        ?string $field = null
    ) {
        $this->setField($field);

        return $this;
    }

    /**
     * @param null|string $field
     * @return void
     */
    private function setField(?string $field = null): void
    {
        $this->field = $field;
    }

    /**
     * @return null|string
     */
    #[\ReturnTypeWillChange]
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param int|string|null $signal
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function signal(
        $signal
    ) {
        $this->setSignal($signal);

        return $this;
    }

    /**
     * @param int|string|null $signal
     * @return void
     */
    private function setSignal($signal): void
    {
        if (! (is_null($signal) || is_string($signal) || is_int($signal))) {
            $signal = null;
        }

        $this->signal = $signal;
    }

    /**
     * @return int|string|null
     */
    #[\ReturnTypeWillChange]
    public function getSignal()
    {
        return $this->signal;
    }

    /**
     * @param int $system
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function system(
        int $system
    ) {
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