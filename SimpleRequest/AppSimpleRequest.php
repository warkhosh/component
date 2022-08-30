<?php

namespace Warkhosh\Component\SimpleRequest;

/**
 * Class AppSimpleRequest
 *
 * @package Warkhosh\Component\SimpleRequest
 */
class AppSimpleRequest
{
    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $method = "GET";

    /**
     * Содержит тип потоковой передачи с значением контента
     *
     * @var array
     */
    protected $stream = [];

    /**
     * Флаг получения заголовков в ответе
     *
     * @var bool
     */
    protected $headerInResponse = false;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @var boolean
     */
    protected $sslChecks = false;

    /**
     * Поля со значениями которые указали для передачи.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * файлы которые указали для передачи.
     *
     * @var array
     */
    protected $files = [];

    /**
     *
     * @var array
     */
    protected $accept = [];

    /**
     * Результат выполнения.
     *
     * @var array
     */
    protected $result = ['errno' => 0, 'error' => '', 'document' => '', 'headers' => [], 'http_code' => 0];

    /**
     * AppSimpleRequest constructor.
     */
    public function __construct()
    {
        $this->initDefault();
    }

    /**
     * Настройки приложения для большинства запросов
     *
     * @return $this
     */
    public function initDefault()
    {
        $this->url = '';
        $this->method = getConfig("spider.setting.default.method", "GET");
        $this->headers = $this->options = $this->cookies = $this->fields = $this->files = $this->accept = [];
        $this->result = ['errno' => 0, 'error' => '', 'document' => '', 'headers' => [], 'http_code' => 0];
        $this->setSslChecks(getConfig("spider.setting.default.ssl_checks", false));

        $this->setReturnTransfer(getConfig("spider.setting.default.return_transfer", true));      // return web page
        $this->setHeadersInOutput(getConfig("spider.setting.default.headers_in_output", true));   // return headers
        $this->setFollowsAnyHeader(getConfig("spider.setting.default.follows_any_header", true)); // follow redirects
        $this->setAcceptEncoding(getConfig("spider.setting.default.accept_encoding",
            ""));        // handle all encodings
        $this->setAutoReferer(getConfig("spider.setting.default.auto_referer",
            true));            // set referer on redirect
        $this->setConnectTimeout(getConfig("spider.setting.default.connect_timeout", 10));        // timeout on connect
        $this->setTimeout(getConfig("spider.setting.default.timeout", 120));                      // timeout on response
        $this->setMaxRedirect(getConfig("spider.setting.default.max_redirect",
            10));              // stop after 10 redirects
        $this->setFreshConnect(getConfig("spider.setting.default.fresh_connect", true));
        $this->setForbidReUse(getConfig("spider.setting.default.forbid_re_use", true));

        if (is_array($headers = getConfig("spider.setting.default.headers", null))) {
            $this->setHeader($headers);
        }

        if (! is_null($userAgent = getConfig("spider.setting.default.user_agent", null))) {
            $this->setUserAgent($userAgent);
        }

        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function get($uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        $this->setMethod("GET");

        return $this->request();
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @param string $referer
     * @return $this
     */
    public function post($fields = [], $uri = null, $referer = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        if (! is_null($referer)) {
            $this->setHeader("Referer: {$referer}");
        }

        $this->setMethod("POST");
        $this->fields($fields);

        return $this->request();
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @return $this
     */
    public function put($fields = [], $uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        $this->setMethod("PUT");
        $this->setCustomRequest("PUT");
        $this->fields($fields);

        return $this->request();
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @return $this
     */
    public function patch($fields = [], $uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        $this->setMethod("PATCH");
        $this->setCustomRequest("PATCH");
        $this->fields($fields);

        return $this->request();
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @return $this
     */
    public function delete($fields = [], $uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        $this->setMethod("DELETE");
        $this->setCustomRequest("DELETE");
        $this->fields($fields);

        return $this->request();
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function head($uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        $this->setMethod("GET");
        $this->setHeadersInOutput(true); // принудительно включаем получение заголовков в результате запроса

        return $this->request();
    }

    /**
     * Устанавливает значения для передачи и урл если передали
     *
     * @param array  $fields
     * @param string $uri
     * @return $this
     */
    public function fields($fields = [], $uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        if (is_array($fields) && count($this->stream) === 0) {
            foreach ($fields as $key => $row) {
                $this->fields[$key] = $row;
            }
        }

        return $this;
    }

    /**
     * Выполнить сеанс
     *
     * @return $this
     */
    public function request()
    {
        $ch = curl_init();

        // Если указали передачу файла но метод не POST, меняем его!
        if (count($this->files) > 0 && $this->method !== "POST") {
            $this->setMethod("POST");
        }

        // Последовательность устаовки этого параметра важна для POST!
        if ($this->method === "POST") {
            $this->options[CURLOPT_POST] = true;

        } elseif ($this->method === "PUT") {
            $this->setCustomRequest("PUT");

        } elseif ($this->method === "PATCH") {
            $this->setCustomRequest("PATCH");

        } elseif ($this->method === "DELETE") {
            $this->setCustomRequest("DELETE");
        }

        // CURLOPT_POSTFIELDS принимает значения в двух форматах, и это установит как будут кодироваться данные:
        // array: данные будут отправляться как multipart/form-data
        // string: данные будут отправляться как application/x-www-form-urlencoded, которая является кодировкой по умолчанию для представленных данных форм.
        if (count($this->files) > 0) {
            $this->options[CURLOPT_POSTFIELDS] = array_merge(count($this->fields) ? $this->fields : [], $this->files);
            $this->setHeader('Content-Type: multipart/form-data');
            $this->setFollowsAnyHeader(true); // follow redirects

        } elseif (count($this->fields) > 0) {
            $this->options[CURLOPT_POSTFIELDS] = http_build_query($this->fields);

        } elseif (count($this->stream) > 0) {
            $this->options[CURLOPT_POSTFIELDS] = $this->stream[1];
        }

        if (count($this->cookies) && is_string($cookies = "")) {
            foreach ($this->cookies as $token => $value) {
                $cookies .= "{$token}={$value}; ";
            }

            $this->options[CURLOPT_COOKIE] = trim($cookies);
        }

        if (count($this->accept) > 0) {
            $this->setHeader("Accept: " . trim(join(", ", $this->accept)));
        }

        if (count($this->headers) > 0) {
            $this->options[CURLOPT_HTTPHEADER] = $this->headers;
        }

        curl_setopt_array($ch, $this->options);

        $document = curl_exec($ch);
        $err = curl_errno($ch);
        $error = curl_error($ch);

        $this->result = curl_getinfo($ch);
        $headerSize = $this->headerInResponse ? curl_getinfo($ch, CURLINFO_HEADER_SIZE) : 0;

        curl_close($ch);

        $this->result['errno'] = intval($err);
        $this->result['error'] = $error;
        $this->result['document'] = trim($document);
        $this->result['headers'] = [];

        if ($this->headerInResponse) {
            $this->result['headers'] = substr($this->result['document'], 0, $headerSize);
            $this->result['headers'] = $headerSize > 0 ? explode("\n", $this->result['headers']) : [];
            $this->result['document'] = substr($this->result['document'], $headerSize);

            // перебираем все заголовки и старые удаляем а добавляем на их основе новые с буквеными ключами
            foreach ($this->result['headers'] as $key => $row) {
                $row = trim($row);

                if ($row === "") {
                    unset($this->result['headers'][$key]);
                    continue;
                }

                $data = explode(":", $row);

                if (count($data) > 1) {
                    $first = array_shift($data);
                    $first = mb_strtolower($first);
                    $this->result['headers'][$first] = trim(join(":", $data));
                    unset($this->result['headers'][$key]);

                    if ($first === 'content-type') {
                        $row = explode(";", $this->result['headers'][$first]);
                        $first = is_string($first = array_shift($row)) ? trim($first) : '';

                        switch ($first) {
                            case 'application/xml':
                                $this->result['headers']['content-type'] = 'xml';
                                break;
                            case 'application/json':
                                $this->result['headers']['content-type'] = 'json';
                                break;
                            default:
                                $this->result['headers']['content-type'] = $first;
                        }

                        $second = is_string($second = array_shift($row)) ? trim($second) : '';
                        $this->result['headers']['content-charset'] = str_replace('charset=', '', $second);
                    }

                } elseif (preg_match("/^HTTP\//is", $row)) {
                    preg_match('/^HTTP\/(.*)/is', $row, $match);
                    $this->result['headers']['http'] = isset($match[1]) ? trim($match[1]) : trim($row);
                    $this->result['headers']['http-version'] = substr($this->result['headers']['http'], 0, 3);
                    unset($this->result['headers'][$key]);
                }
            }
        }

        return $this;
    }

    /**
     * Возращает значения указаного заголовка из ответа сервера
     *
     * @param string $key
     * @param mixed  $default
     * @return string
     */
    public function getHeader($key, $default = "")
    {
        if (is_string($key) && isset($this->result['headers']) && is_array($this->result['headers'])) {
            return array_key_exists($key, $this->result['headers']) ? $this->result['headers'][$key] : $default;
        }

        return $default;
    }

    /**
     * Возращает список всех заголовков в ответе
     *
     * @return array
     */
    public function getHeaders()
    {
        return is_array($this->result['headers']) ? $this->result['headers'] : [];
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
        return $this->result['errno'];
    }

    /**
     * @return integer
     */
    public function getErrno()
    {
        return $this->result['errno'];
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->result['error'];
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
                return json_decode($this->result['document'], true);

            }

            // Если тип ответа в формате JSON, нужно преобразовать его в объект stdClass
            if ($type === 'toObject') {
                return json_decode($this->result['document'], false);
            }

            return $this->result['document'];

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
            if ($cacheDocument !== $this->result['document']) {
                $cacheDocument = $this->result['document'];
                $cached = false;

            } else {
                $cached = true;
            }

            if ($this->getHeader('content-type') === 'json') {
                $data = $cached ? $data : json_decode($this->result['document'], true);

                return \Warkhosh\Variable\VarArray::get($key, $data, $default);
            }

            return $default;

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Возращает код HTTP ответа который получаем используя curl_getinfo(resource).
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return isset($this->result['http_code']) ? intval($this->result['http_code']) : 0;
    }

    /**
     * HTTP-авторизация.
     *
     * @param string $user
     * @param string $password
     * @return $this
     */
    public function httpAuth($user, $password)
    {
        $this->setHttpAuth($user, $password);

        return $this;
    }

    /**
     * HTTP-авторизация.
     *
     * @param string $user
     * @param string $password
     * @return void
     */
    protected function setHttpAuth($user, $password)
    {
        $encodedAuth = base64_encode($user . ":" . $password);

        $this->setHeader("Authentication : Basic " . $encodedAuth);
        $this->options[CURLOPT_USERPWD] = $user . ":" . $password;
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
    }

    /**
     * Устанавливает для передачи указанный файл.
     *
     * @param string            $path     - путь до файла на диске
     * @param null|string|array $name     - Имя файла в данных для загрузки
     * @param string            $mimeType - MIME-тип файла ( по умолчанию это application/octet-stream )
     * @return $this
     */
    public function file($path, $name = null, $mimeType = 'application/octet-stream')
    {
        $this->setFile($path, $name, $mimeType);

        return $this;
    }

    /**
     * Устанавливает для передачи указанный файл.
     *
     * @param string            $path     - путь до файла на диске
     * @param null|string|array $name     - Имя файла в данных для загрузки
     * @param string            $mimeType - MIME-тип файла ( по умолчанию это application/octet-stream )
     * @return void
     */
    protected function setFile($path = null, $name = null, $mimeType = 'application/octet-stream')
    {
        if (! is_null($path) && file_exists($path)) {
            if (is_array($name)) {
                $field = array_shift($name);
                $name = (string)array_shift($name);
                $field = empty($field) ? "file_" . (count($this->files) + 1) : $field;
                $name = empty($name) ? "file_" . (count($this->files) + 1) : $name;
                $this->files[$field] = new \CURLFile($path, $mimeType, $name);

            } else {
                $name = is_null($name) ? "file_" . (count($this->files) + 1) : $name;
                $this->files[$name] = new \CURLFile($path, $mimeType, $name);
            }
        }
    }

    /**
     * Устанавливает содержимое заголовка "Cookie", который будет отправлен с HTTP запросом.
     *
     * @param array $cookies
     * @return $this
     */
    public function cookies($cookies)
    {
        $this->setCookies($cookies);

        return $this;
    }

    /**
     * Устанавливает содержимое заголовка "Cookie", который будет отправлен с HTTP запросом.
     *
     * @param array $cookies
     * @return void
     */
    protected function setCookies($cookies)
    {
        if (is_array($cookies)) {
            $this->cookies = array_merge($this->cookies, $cookies);
        }
    }

    /**
     * Передать указанный массив как поток данных в формате JSON.
     *
     * @note метод устанавливает дополнительне параметры для передачи!
     *
     * @param array | string $data
     * @param string         $uri
     * @return $this
     */
    public function streamJson($data, $uri = null)
    {
        $this->setStreamJson($data, $uri);

        return $this;
    }

    /**
     * Передать указанный массив как поток данных в формате JSON.
     *
     * @note метод устанавливает дополнительне параметры для передачи!
     *
     * @param array | string $data
     * @param string         $uri
     * @return void
     */
    protected function setStreamJson($data, $uri = null)
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        if (is_array($data)) {
            $data = json_encode($data);
        }

        $this->files = $this->fields = [];
        $this->stream = ["json", $data];

        $this->setMethod("POST");
        $this->setCustomRequest("POST");
        $this->setReturnTransfer(true);
        $this->setHeader(['Content-Type: application/json', 'Content-Length: ' . strlen($data)]);
        $this->accept('application/json');
    }

    /**
     * Передать указанный массив как поток данных в формате XML.
     *
     * @note метод устанавливает дополнительне параметры для передачи!
     *
     * @param array | string $data
     * @param string         $uri
     * @return $this
     */
    public function streamXml($data, $uri = null)
    {
        $this->setStreamXml($data, $uri);

        return $this;
    }

    /**
     * Передать указанный массив как поток данных в формате XML.
     *
     * @note метод устанавливает дополнительне параметры для передачи!
     *
     * @param array | string $data
     * @param null           $uri
     * @param string         $rootNode
     * @return void
     */
    protected function setStreamXml($data, $uri = null, $rootNode = 'root')
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        if (is_array($data)) {
            $dom = new \DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($xml = array_to_xml($data, new \SimpleXMLElement("<{$rootNode}/>"))->asXML());
            $dom->formatOutput = true;
            $data = $dom->saveXml();
        }

        $this->files = $this->fields = [];
        $this->stream = ["xml", $data];

        $this->setMethod("POST");
        $this->setCustomRequest("POST");
        $this->setReturnTransfer(true);
        $this->setHeader(['Content-Type: application/xml', 'Content-Length: ' . strlen($data)]);
        $this->accept('application/xml');
    }

    /**
     * Устанавливает метод был использования в запросе.
     *
     * @param string $str
     * @return $this
     */
    public function method($str)
    {
        $this->setMethod($str);

        return $this;
    }

    /**
     * Устанавливает метод был использования в запросе.
     *
     * @param string $str
     */
    protected function setMethod($str)
    {
        if (is_string($str)) {
            $this->method = in_array($str, ["GET", "POST", "PUT", "PATCH", "DELETE"]) ? $str : "GET";
        }
    }

    /**
     * Устанавливает URI для текущего сеанса.
     *
     * @param string $str
     * @return $this
     */
    public function url($str)
    {
        $this->setUrl($str);

        return $this;
    }

    /**
     * Устанавливает URI для текущего сеанса.
     *
     * @param string $str
     * @return void
     */
    protected function setUrl($str)
    {
        $this->url = $this->options[CURLOPT_URL] = (string)$str;
        $this->settingsForURI($str);
    }

    /**
     * Возвращает URI текущего сеанса.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $uri
     * @return $this
     */
    protected function settingsForURI($uri)
    {
        if (strtolower((substr($uri, 0, 5)) === 'https')) {
            if ($this->sslChecks) {
                $this->setSslValidation(true);
                $this->setSslValidationHost(1);
            } else {
                $this->setSslValidation(false);
                $this->setSslValidationHost(0);
            }
        }

        return $this;
    }

    /**
     * @param string|array $headers
     * @return $this
     */
    public function header($headers)
    {
        $this->setHeader($headers);

        return $this;
    }

    /**
     * @param string | array $headers
     * @return void
     */
    protected function setHeader($headers)
    {
        if (is_array($headers)) {
            foreach ($headers as $row) {
                $this->headers[] = $row;
            }
        }

        if (is_string($headers)) {
            $this->headers[] = $headers;
        }
    }

    /**
     * Установка флага как отдать результат передачи в качестве строки из curl_exec()
     *
     * @note если указать false то результат будет напрямую выведен в браузер
     *
     * @param boolean $flag
     * @return $this
     */
    public function returnTransfer($flag)
    {
        $this->setReturnTransfer($flag);

        return $this;
    }

    /**
     * Установка флага как отдать результат передачи в качестве строки из curl_exec()
     *
     * @note если указать false то результат будет напрямую выведен в браузер
     *
     * @param boolean $flag
     * @return void
     */
    protected function setReturnTransfer($flag)
    {
        $this->options[CURLOPT_RETURNTRANSFER] = (boolean)$flag;
    }

    /**
     * Включение\выключение заголовков в выводе результата запроса.
     *
     * @param boolean $flag
     * @return $this
     */
    public function headersInOutput($flag)
    {
        $this->setHeadersInOutput($flag);

        return $this;
    }

    /**
     * Включение\выключение заголовков в выводе результата запроса.
     *
     * @param boolean $flag
     * @return void
     */
    protected function setHeadersInOutput($flag)
    {
        $this->options[CURLOPT_HEADER] = $this->headerInResponse = (boolean)$flag;
    }

    /**
     * Определение следования любому заголовку "Location" в ответе.
     *
     * @param boolean $flag
     * @return $this
     */
    public function followsAnyHeader($flag)
    {
        $this->setFollowsAnyHeader($flag);

        return $this;
    }

    /**
     * Определение следования любому заголовку "Location" в ответе.
     *
     * @param boolean $flag
     * @return void
     */
    protected function setFollowsAnyHeader($flag)
    {
        $this->options[CURLOPT_FOLLOWLOCATION] = (boolean)$flag;
    }

    /**
     * Включает декодирование запроса: Accept-Encoding:...
     *
     * @note поддерживаемыми кодировками являются "identity", "deflate" и "gzip".
     *
     * @param string $encoding
     * @return $this
     */
    public function acceptEncoding($encoding)
    {
        $this->setAcceptEncoding($encoding);

        return $this;
    }

    /**
     * Включает декодирование запроса: Accept-Encoding:...
     *
     * @note поддерживаемыми кодировками являются "identity", "deflate" и "gzip".
     *
     * @param string $encoding
     * @return void
     */
    protected function setAcceptEncoding($encoding)
    {
        $this->options[CURLOPT_ENCODING] = (string)$encoding;
    }

    /**
     * Задает значение HTTP заголовка: User-Agent:...
     *
     * @param string $userAgent
     * @return $this
     */
    public function userAgent($userAgent)
    {
        $this->setUserAgent($userAgent);

        return $this;
    }

    /**
     * Задает значение HTTP заголовка: User-Agent:...
     *
     * @param string $userAgent
     * @return void
     */
    protected function setUserAgent($userAgent)
    {
        $this->options[CURLOPT_USERAGENT] = (string)$userAgent;
        $this->setHeader("User-Agent: " . (string)$userAgent);
    }

    /**
     * Задает MIME тип.
     *
     * @param string | array $str
     * @return $this
     */
    public function accept($str)
    {
        $this->setAccept($str);

        return $this;
    }

    /**
     * Задает MIME тип.
     *
     * @param string $str
     * @return void
     */
    protected function setAccept($str)
    {
        if (is_array($str)) {
            foreach ($str as $accept) {
                $accept = trim($accept);
                $this->accept[$accept] = $accept;
            }

        } elseif (is_string($str) && ($str = trim($str)) !== '') {
            $this->accept[$str] = $str;
        }
    }

    /**
     * Включает\выключает установку поля Referer: в перенаправленных запросах.
     *
     * @param boolean $flag
     * @return $this
     */
    public function autoReferer($flag)
    {
        $this->setAutoReferer($flag);

        return $this;
    }

    /**
     * Включает\выключает установку поля Referer: в перенаправленных запросах.
     *
     * @param boolean $flag
     * @return void
     */
    protected function setAutoReferer($flag)
    {
        $this->options[CURLOPT_AUTOREFERER] = (boolean)$flag;
    }

    /**
     * Устанавливает таймаут соединения в секундах, которые ожидаются при попытке подключения.
     *
     * @note используйте 0 чтобы ждать бесконечно.
     *
     * @param integer $seconds
     * @return $this
     */
    public function connectTimeout($seconds)
    {
        $this->setConnectTimeout($seconds);

        return $this;
    }

    /**
     * Устанавливает таймаут соединения в секундах, которые ожидаются при попытке подключения.
     *
     * @note используйте 0 чтобы ждать бесконечно.
     *
     * @param integer $seconds
     * @return void
     */
    protected function setConnectTimeout($seconds)
    {
        $this->options[CURLOPT_CONNECTTIMEOUT] = (int)$seconds;
    }

    /**
     * Устанавливает таймаут ответа в секундах, которое отводятся для работы CURL-функций.
     *
     * @param integer $seconds
     * @return $this
     */
    public function timeout($seconds)
    {
        $this->setTimeout($seconds);

        return $this;
    }

    /**
     * Устанавливает таймаут ответа в секундах, которое отводятся для работы CURL-функций.
     *
     * @param integer $seconds
     * @return void
     */
    protected function setTimeout($seconds)
    {
        $this->options[CURLOPT_TIMEOUT] = (int)$seconds;
    }

    /**
     * Устанавливает максимальное количество принимаемых редиректов.
     *
     * @note Используйте этот параметр вместе с параметром CURLOPT_FOLLOWLOCATION
     *
     * @param integer $num
     * @return $this
     */
    public function maxRedirect($num)
    {
        $this->setMaxRedirect($num);

        return $this;
    }

    /**
     * Устанавливает максимальное количество принимаемых редиректов.
     *
     * @note Используйте этот параметр вместе с параметром CURLOPT_FOLLOWLOCATION
     *
     * @param integer $num
     * @return void
     */
    protected function setMaxRedirect($num)
    {
        $this->options[CURLOPT_MAXREDIRS] = (int)$num;
    }

    /**
     * Устанавливает флаг начать новую "сессию" cookies.
     *
     * @param boolean $flag
     * @return $this
     */
    public function newSession($flag)
    {
        $this->setNewSession($flag);

        return $this;
    }

    /**
     * Устанавливает флаг начать новую "сессию" cookies.
     *
     * @param boolean $flag
     * @return void
     */
    protected function setNewSession($flag)
    {
        $this->options[CURLOPT_COOKIESESSION] = (boolean)$flag;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного.
     *
     * @param boolean $flag
     * @return $this
     */
    public function freshConnect($flag)
    {
        $this->setFreshConnect($flag);

        return $this;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного.
     *
     * @param boolean $flag
     * @return void
     */
    protected function setFreshConnect($flag)
    {
        $this->options[CURLOPT_FRESH_CONNECT] = (boolean)$flag;
    }

    /**
     * Устанавливает флаг принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно
     *
     * @param boolean $flag
     * @return $this
     */
    public function forbidReUse($flag)
    {
        $this->setForbidReUse($flag);

        return $this;
    }

    /**
     * Устанавливает флаг принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно
     *
     * @param boolean $flag
     * @return void
     */
    protected function setForbidReUse($flag)
    {
        $this->options[CURLOPT_FORBID_REUSE] = (boolean)$flag;
    }

    /**
     * Устанавливает название специального метода, который будет использован в HTTP запросе вместо GET или HEAD.
     *
     * @param string $name
     * @return $this
     */
    public function customRequest($name)
    {
        $this->setCustomRequest($name);

        return $this;
    }

    /**
     * Устанавливает название специального метода, который будет использован в HTTP запросе вместо GET или HEAD.
     *
     * @param string $name
     * @return void
     */
    protected function setCustomRequest($name)
    {
        $this->options[CURLOPT_CUSTOMREQUEST] = strtoupper((string)$name);
    }

    /**
     * Устанавливает флаг проверки сертификата удаленного сервера.
     *
     * @note начиная с curl 7.10, по умолчанию этот параметр имеет значение TRUE
     * @note если CURLOPT_SSL_VERIFYPEER установлен в FALSE, возможно потребуется установить CURLOPT_SSL_VERIFYHOST в TRUE или FALSE
     *
     * @param boolean $flag
     * @return $this
     */
    public function sslValidation($flag)
    {
        $this->setSslValidation($flag);

        return $this;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного.
     *
     * @param boolean $flag
     * @return void
     */
    protected function setSslValidation($flag)
    {
        $this->options[CURLOPT_SSL_VERIFYPEER] = (boolean)$flag;
    }

    /**
     * Устанавливает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения.
     *
     * @note значение 1 означает проверку существования имени, значение 2 - кроме того, и проверку соответствия имени хоста.
     *
     * @param integer $num
     * @return $this
     */
    public function sslValidationHost($num)
    {
        $this->setSslValidationHost($num);

        return $this;
    }

    /**
     * Устанавливает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения.
     *
     * @note значение 1 означает проверку существования имени, значение 2 - кроме того, и проверку соответствия имени хоста.
     *
     * @param integer $num
     * @return void
     */
    protected function setSslValidationHost($num)
    {
        $this->options[CURLOPT_SSL_VERIFYHOST] = (int)$num;
    }

    /**
     * Установка флага включения или выключения SSL сертификата урла если тот https
     *
     * @param boolean $flag
     * @return $this
     */
    public function sslChecks($flag)
    {
        $this->setSslValidationHost($flag);

        return $this;
    }

    /**
     * @param $flag
     */
    protected function setSslChecks($flag)
    {
        $this->sslChecks = (boolean)$flag;
    }
}