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

    public function __construct(string $message = "", int $code = 1, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
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
     * @param string $field
     * @return $this
     */
    public function field(string $field)
    {
        $this->setField($field);

        return $this;
    }

    /**
     * @param string $field
     * @return void
     */
    private function setField(string $field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}