<?php

namespace Warkhosh\Component\Std;

class ExceptionDataProvider extends \Exception
{
    /** The error message */
    protected $message;

    /** The error code */
    protected $code;

    /** The filename where the error happened  */
    protected $file;

    /** The line where the error happened */
    protected $line;

    /** Название поля */
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
     * @param string         $message
     * @param int            $code
     * @param \Throwable|null $previous
     * @return \Warkhosh\Component\Std\ExceptionDataProvider
     */
    public static function init($message = "", $code = 0, \Throwable $previous = null)
    {
        return new static($message, $code, $previous);
    }

    /**
     * @param string $file
     * @return $this
     */
    public function file(string $file)
    {
        $this->setFile($file);

        return $this;
    }

    /**
     * @param string $file
     * @return void
     */
    private function setFile(string $file)
    {
        $this->file = $file;
    }

    /**
     * @param string $line
     * @return $this
     */
    public function line(string $line)
    {
        $this->setLine($line);

        return $this;
    }

    /**
     * @param string $line
     * @return void
     */
    private function setLine(string $line)
    {
        $this->line = $line;
    }

    /**
     * @param null|string $field
     * @return $this
     */
    public function field(?string $field = null)
    {
        $this->setField($field);

        return $this;
    }

    /**
     * @param null|string $field
     * @return void
     */
    private function setField(?string $field = null)
    {
        $this->field = $field;
    }

    /**
     * @return null|string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param int|string|null $signal
     * @return $this
     */
    public function signal($signal)
    {
        $this->setSignal($signal);

        return $this;
    }

    /**
     * @param int|string|null $signal
     * @return void
     */
    private function setSignal($signal)
    {
        if (! (is_null($signal) || is_string($signal) || is_int($signal))) {
            $signal = null;
        }

        $this->signal = $signal;
    }

    /**
     * @return int|string|null
     */
    public function getSignal()
    {
        return $this->signal;
    }

    /**
     * @param int $system
     * @return $this
     */
    public function system(int $system)
    {
        $this->setSystem($system);

        return $this;
    }

    /**
     * @param int $system
     * @return void
     */
    private function setSystem(int $system)
    {
        $this->system = $system;
    }

    /**
     * @return int
     */
    public function getSystem()
    {
        return $this->system;
    }
}