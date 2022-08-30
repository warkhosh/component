<?php

namespace Warkhosh\Component\SimpleRequest;

use Warkhosh\Variable\VarArray;

class AppSimpleResponse implements \Psr\Http\Message\ResponseInterface {

    protected $response = ['errno' => 0, 'error' => '', 'document' => '', 'headers' => [], 'http_code' => 0];

    /**
     * AppSimpleResponse constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Возвращает значения указанного заголовка из ответа сервера
     *
     * @param string $key
     * @param mixed  $default
     * @return string
     */
    public function getHeader($key, $default = "")
    {
        if (is_string($key) && isset($this->response['headers']) && is_array($this->response['headers'])) {
            return array_key_exists($key, $this->response['headers']) ? $this->response['headers'][$key] : $default;
        }

        return $default;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name) {
        return key_exists($name, $this->response['headers']);
    }

    /**
     * Возвращает список всех заголовков в ответе
     *
     * @return array
     */
    public function getHeaders()
    {
        return is_array($this->response['headers']) ? $this->response['headers'] : [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name) {
        $header = $this->getHeader($name);

        return is_array($header) ? join(",", $header) : strval($header);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value) {
        throw new \InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value) {
        throw new \InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     * @throws \Exception
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     */
    public function withoutHeader($name) {
        throw new \Exception("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Сокращенный вариант проверки.
     *
     * @param int $code
     * @return bool
     */
    public function getResult($code = 200)
    {
        return ($this->getErrno() === 0 && $this->getStatusCode() === $code);
    }

    /**
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->response['errno'];
    }

    /**
     * @return integer
     */
    public function getErrno()
    {
        return $this->response['errno'];
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->response['error'];
    }

    /**
     * @param string $type
     * @return string|array|\stdClass
     * @throws \Throwable
     */
    public function getDocument($type = 'raw')
    {
        try {
            // Если тип ответа в формате JSON нужно превратить в массив
            if ($type === 'toArray') {
                return json_decode($this->response['document'], true);

            }

            // Если тип ответа в формате JSON, нужно преобразовать его в объект stdClass
            if ($type === 'toObject') {
                return json_decode($this->response['document'], false);
            }

            return $this->response['document'];

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Обращение за данными в JSON ответе по ключу.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed|null
     * @throws \Throwable
     */
    public function getDocumentValue($key = '', $default = null)
    {
        static $cacheDocument, $data;

        try {
            if ($cacheDocument !== $this->response['document']) {
                $cacheDocument = $this->response['document'];
                $cached = false;

            } else {
                $cached = true;
            }

            if ($this->getHeader('Content-Type') === 'json') {
                $data = $cached ? $data : json_decode($this->response['document'], true);

                return \Warkhosh\Variable\VarArray::get($key, $data, $default);
            }

            return $default;

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Gets the body of the message.
     *
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @return \Psr\Http\Message\StreamInterface Returns the body as a stream.
     * @throws \Exception
     */
    public function getBody() {
        throw new \Exception("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance with the specified message body.
     *
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @param \Psr\Http\Message\StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(\Psr\Http\Message\StreamInterface $body) {
        throw new \InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Возвращает код HTTP ответа который получаем используя curl_getinfo(resource).
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return isset($this->response['http_code']) ? intval($this->response['http_code']) : 0;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion() {
        return (string)VarArray::get("headers.http-version", $this->response, "");
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version) {
        throw new \InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @todo нужно будет более глубже понять работу этого метода а пока сделал заглушку для работы интерфейса
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '') {
        throw new \InvalidArgumentException("Этот метод не поддерживается в этой редакции кода");
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase() {
        return "";
    }
}