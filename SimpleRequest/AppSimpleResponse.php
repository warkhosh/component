<?php

namespace Warkhosh\Component\SimpleRequest;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use stdClass;
use Throwable;
use Warkhosh\Variable\VarArray;

class AppSimpleResponse implements \Psr\Http\Message\ResponseInterface
{
    protected array $response = [
        'errno' => 0,
        'error' => '',
        'document' => '',
        'headers' => [],
        'http_code' => 0,
    ];

    /**
     * @var AppSimpleResponseStream
     */
    protected AppSimpleResponseStream $stream;

    /**
     * AppSimpleResponse constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $response['errno'] = (int)VarArray::get("errno", $response, 0);
        $response['error'] = (string)VarArray::get("error", $response, "");
        $response['document'] = (string)VarArray::get("document", $response, "");
        $response['headers'] = (array)VarArray::get("headers", $response, []);
        $response['http_code'] = (int)VarArray::get("http_code", $response, 0);

        $this->response = $response;
        $this->stream = new AppSimpleResponseStream($this->response['document']);
    }

    /**
     * Возвращает значения указанного заголовка из ответа сервера
     *
     * @param string $name
     * @return array
     */
    public function getHeader(string $name): array
    {
        if (! empty($name)) {
            $name = trim(mb_strtolower($name));
            $name = str_replace(" ", "-", ucwords(str_replace("-", " ", $name)));

            if (array_key_exists($name, $this->response['headers'])) {
                return $this->response['headers'][$name];
            }
        }

        return [];
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header name using a case-insensitive string comparison.
     *              Returns false if no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool
    {
        $name = trim(mb_strtolower($name));
        $name = str_replace(" ", "-", ucwords(str_replace("-", " ", $name)));

        return key_exists($name, $this->response['headers']);
    }

    /**
     * Возвращает список всех заголовков в ответе
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return is_array($this->response['headers']) ? $this->response['headers'] : [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header concatenated together using a comma.
     *                If the header does not appear in the message, this method MUST return an empty string.
     */
    public function getHeaderLine(string $name): string
    {
        $header = $this->getHeader($name);

        return join(",", $header);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        throw new InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        throw new InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     * @throws Exception
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     */
    public function withoutHeader(string $name): MessageInterface
    {
        throw new Exception("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Сокращенный вариант проверки.
     *
     * @param int $code
     * @return bool
     */
    public function getResult(int $code = 200): bool
    {
        return ($this->getErrno() === 0 && $this->getStatusCode() === $code);
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->response['errno'];
    }

    /**
     * @return int
     */
    public function getErrno(): int
    {
        return $this->response['errno'];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->response['error'];
    }

    /**
     * @param string $type
     * @return array|stdClass|string
     * @throws Throwable
     * @deprecated концепция получения контента документа изменилась и этот метод будет удален!
     */
    public function getDocument(string $type = 'raw'): array|string|stdClass
    {
        // Если тип ответа в формате JSON нужно превратить в массив
        if ($type === 'toArray') {
            return json_decode($this->response['document'], true);

        }

        // Если тип ответа в формате JSON, нужно преобразовать его в объект stdClass
        if ($type === 'toObject') {
            return json_decode($this->response['document'], false);
        }

        return $this->getBody()->getContents();
    }

    /**
     * Обращение за данными в JSON ответе по ключу.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     * @throws Throwable
     * @deprecated концепция получения контента документа изменилась и этот метод будет удален!
     */
    public function getDocumentValue(string $key = '', mixed $default = null): mixed
    {
        static $cacheDocument, $data;

        if ($cacheDocument !== $this->response['document']) {
            $cacheDocument = $this->response['document'];
            $cached = false;

        } else {
            $cached = true;
        }

        if ($this->getHeader('Content-Type') === 'json') {
            $data = $cached ? $data : json_decode($this->response['document'], true);

            return VarArray::get($key, $data, $default);
        }

        return $default;
    }

    /**
     * Получает тело сообщения в виде потока
     *
     * @return StreamInterface
     * @throws Exception
     */
    public function getBody(): StreamInterface
    {
        if (! $this->stream) {
            $this->stream = AppSimpleResponseStream::stream('');
        }

        return $this->stream;
    }

    /**
     * Вернуть экземпляр с указанным телом сообщения
     *
     * @param StreamInterface $body Body.
     * @return MessageInterface
     * @throws InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Возвращает код HTTP ответа который получаем используя curl_getinfo(resource).
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return isset($this->response['http_code']) ? intval($this->response['http_code']) : 0;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return (string)VarArray::get("headers.http-version", $this->response, "");
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        throw new InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return ResponseInterface
     * @throws InvalidArgumentException For invalid status code arguments.
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        throw new InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return string Reason phrase; must return an empty string if none present.
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @throws Exception
     */
    public function getReasonPhrase(): string
    {
        throw new Exception("Этот метод не поддерживается в этой редакции кода");
    }
}
