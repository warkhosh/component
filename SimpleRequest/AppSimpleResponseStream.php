<?php

namespace Warkhosh\Component\SimpleRequest;

use Warkhosh\Variable\VarArray;

class AppSimpleResponseStream implements \Psr\Http\Message\StreamInterface
{
    /**
     * @see http://php.net/manual/function.fopen.php
     * @see http://php.net/manual/en/function.gzopen.php
     */
    private const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';
    private const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

    /** @var resource */
    private $stream;

    /** @var int|null */
    private $size;

    /** @var bool */
    private $seekable;

    /** @var bool */
    private $readable;

    /** @var bool */
    private $writable;

    /** @var string|null */
    private $uri;

    /** @var mixed[] */
    private $metaData;

    /**
     * @param string|resource $resource
     * @param array{size?: int, meta_Data?: array} $options Associative
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $resource, array $options = [])
    {
        if (! (is_string($resource) || is_resource($resource))) {
            throw new \RuntimeException('Invalid resource type');
        }

        if (is_string($resource)) {
            $stream = static::tryFopen('php://temp', 'r+');

            if ($resource !== '') {
                fwrite($stream, (string)$resource);
                fseek($stream, 0);
            }

        } else {
            $stream = $resource;

            if ((\stream_get_meta_data($resource)['uri'] ?? '') === 'php://input') {
                $stream = static::tryFopen('php://temp', 'w+');
                stream_copy_to_stream($resource, $stream);
                fseek($stream, 0);
                $resource = $stream;
            }
        }

        if (isset($options['size'])) {
            $this->size = $options['size'];

        } else {
            $fstat = fstat($stream);
            $options['size'] = (int)VarArray::get("size", array_slice($fstat, 13), 0);
        }

        $this->metaData = $options['meta_data'] ?? [];
        $this->stream = $stream;
        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = (bool)preg_match(self::READABLE_MODES, $meta['mode']);
        $this->writable = (bool)preg_match(self::WRITABLE_MODES, $meta['mode']);
        $this->uri = $this->getMetadata('uri');
    }

    /**
     * Закрывает поток при уничтожении
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (! $this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        return static::tryToGetContents($this->stream);
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }

            $this->detach();
        }
    }

    /**
     * @return resource|null
     */
    #[\ReturnTypeWillChange]
    public function detach()
    {
        if (! isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * @return int|null
     */
    #[\ReturnTypeWillChange]
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (! isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (is_array($stats) && isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        return feof($this->stream);
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * Устанавливает смещение к началу потока
     *
     * @return void
     * @throws \RuntimeException on failure.
     * @link http://www.php.net/manual/en/function.fseek.php
     * @see  seek()
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Устанавливает позицию в потоке
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset
     * @param int $whence
     * @return void
     * @throws \RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $whence = (int)$whence;

        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (! $this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException("Unable to seek to stream position {$offset} with whence "
                . var_export($whence, true));
        }
    }

    /**
     * Чтение данных из потока
     *
     * @param int $length
     * @return string
     * @throws \RuntimeException
     */
    public function read($length): string
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (! $this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }

        if (0 === $length) {
            return '';
        }

        try {
            $string = fread($this->stream, $length);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to read from stream', 0, $e);
        }

        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * @param string $string
     * @return int
     * @throws \RuntimeException
     */
    public function write($string): int
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (! $this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;
        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key
     * @return array|mixed|null
     */
    #[\ReturnTypeWillChange]
    public function getMetadata($key = null)
    {
        if (! isset($this->stream)) {
            return $key ? null : [];

        } elseif (! $key) {
            return $this->metaData + stream_get_meta_data($this->stream);

        } elseif (isset($this->metaData[$key])) {
            return $this->metaData[$key];
        }

        $meta = stream_get_meta_data($this->stream);

        return $meta[$key] ?? null;
    }

    /**
     * @param resource|string|int|float|bool|null $resource
     * @return resource
     * @throws \InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public static function stream($resource = '')
    {
        if (is_scalar($resource)) {
            $stream = static::tryFopen('php://temp', 'r+');

            if ($resource !== '') {
                fwrite($stream, (string)$resource);
                fseek($stream, 0);
            }

            return $stream;

        } elseif (is_resource($resource)) {
            $stream = $resource;

            if ((\stream_get_meta_data($resource)['uri'] ?? '') === 'php://input') {
                $stream = static::tryFopen('php://temp', 'w+');
                stream_copy_to_stream($resource, $stream);
                fseek($stream, 0);
            }

            return $stream;

        } elseif (is_null($resource)) {
            return static::tryFopen('php://temp', 'r+');
        }

        throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return resource
     * @throws \RuntimeException
     */
    #[\ReturnTypeWillChange]
    public static function tryFopen(string $filename, string $mode)
    {
        $ex = null;
        set_error_handler(static function (int $errno, string $errstr) use ($filename, $mode, &$ex) {
            $ex = new \RuntimeException(sprintf(
                'Unable to open "%s" using mode "%s": %s',
                $filename,
                $mode,
                $errstr
            ));

            return true;
        });

        try {
            /** @var resource $handle */
            $handle = fopen($filename, $mode);
        } catch (\Throwable $e) {
            $ex = new \RuntimeException(sprintf(
                'Unable to open "%s" using mode "%s": %s',
                $filename,
                $mode,
                $e->getMessage()
            ), 0, $e);
        }

        restore_error_handler();

        if ($ex) {
            /** @var $ex \RuntimeException */
            throw $ex;
        }

        return $handle;
    }

    /**
     * @param resource $stream
     * @return string
     * @throws \RuntimeException
     */
    public static function tryToGetContents($stream): string
    {
        $ex = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$ex) {
            $ex = new \RuntimeException(sprintf(
                'Unable to read stream contents: %s',
                $errstr
            ));

            return true;
        });

        try {
            /** @var string|false $contents */
            $contents = stream_get_contents($stream);

            if ($contents === false) {
                $ex = new \RuntimeException('Unable to read stream contents');
            }
        } catch (\Throwable $e) {
            $ex = new \RuntimeException(sprintf(
                'Unable to read stream contents: %s',
                $e->getMessage()
            ), 0, $e);
        }

        restore_error_handler();

        if ($ex) {
            /** @var $ex \RuntimeException */
            throw $ex;
        }

        return $contents;
    }
}