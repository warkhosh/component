<?php

namespace Warkhosh\Component\SimpleRequest;

use Throwable;

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
    protected $result = [
        'errno'     => 0,
        'error'     => '',
        'document'  => '',
        'stream'    => null,
        'headers'   => [],
        'http_code' => 0,
    ];

    /**
     * AppSimpleRequest constructor.
     */
    public function __construct()
    {
        $this->initDefault();
    }

    /**
     * @return static
     */
    #[\ReturnTypeWillChange]
    public static function init() {
        return new static();
    }

    /**
     * Настройки приложения для большинства запросов
     *
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
        $this->setAcceptEncoding(getConfig("spider.setting.default.accept_encoding", ""));        // handle all encoding
        $this->setAutoReferer(getConfig("spider.setting.default.auto_referer", true));        // set referer on redirect
        $this->setConnectTimeout(getConfig("spider.setting.default.connect_timeout", 10));    // timeout on connect
        $this->setTimeout(getConfig("spider.setting.default.timeout", 120));                  // timeout on response
        $this->setMaxRedirect(getConfig("spider.setting.default.max_redirect", 10));          // stop after 10 redirects
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
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function get($uri = null)
    {
        try {
            if (! is_null($uri)) {
                $this->setUrl($uri);
            }

            $this->setMethod("GET");

            return $this->request();

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @param string $referer
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function post($fields = [], $uri = null, $referer = null)
    {
        try {
            if (! is_null($uri)) {
                $this->setUrl($uri);
            }

            if (! is_null($referer)) {
                $this->setHeader("Referer: {$referer}");
            }

            $this->setMethod("POST");
            $this->fields($fields);

            return $this->request();

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function put($fields = [], $uri = null)
    {
        try {
            if (! is_null($uri)) {
                $this->setUrl($uri);
            }

            $this->setMethod("PUT");
            $this->setCustomRequest("PUT");
            $this->fields($fields);

            return $this->request();

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function patch($fields = [], $uri = null)
    {
        try {
            if (! is_null($uri)) {
                $this->setUrl($uri);
            }

            $this->setMethod("PATCH");
            $this->setCustomRequest("PATCH");
            $this->fields($fields);

            return $this->request();

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param array  $fields
     * @param string $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function delete($fields = [], $uri = null)
    {
        try {
            if (! is_null($uri)) {
                $this->setUrl($uri);
            }

            $this->setMethod("DELETE");
            $this->setCustomRequest("DELETE");
            $this->fields($fields);

            return $this->request();

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param string $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function head($uri = null)
    {
        try {
            if (! is_null($uri)) {
                $this->setUrl($uri);
            }

            $this->setMethod("GET");
            $this->setHeadersInOutput(true); // принудительно включаем получение заголовков в результате запроса

            return $this->request();

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Устанавливает значения для передачи и урл если передали
     *
     * @param array  $fields
     * @param string $uri
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
     * @return AppSimpleResponse
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function request()
    {
        try {
            $ch = curl_init();

            // Если указали передачу файла но метод не POST, меняем его!
            if (count($this->files) > 0 && $this->method !== "POST") {
                $this->setMethod("POST");
            }

            // Последовательность установки этого параметра важна для POST!
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
                $this->options[CURLOPT_POSTFIELDS] = array_merge(count($this->fields) ? $this->fields
                    : [], $this->files);
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
                $this->options[CURLOPT_HTTPHEADER] = array_values($this->headers);
            }

            curl_setopt_array($ch, $this->options);

            $document = curl_exec($ch);
            $err = curl_errno($ch);
            $error = curl_error($ch);
            $result = curl_getinfo($ch);
            $headerSize = $this->headerInResponse ? curl_getinfo($ch, CURLINFO_HEADER_SIZE) : 0;

            curl_close($ch);

            $result['errno'] = intval($err);
            $result['error'] = $error;
            $result['document'] = trim($document);
            $result['headers'] = [];

            if ($this->headerInResponse) {
                $result['headers'] = substr($result['document'], 0, $headerSize);
                $result['headers'] = $headerSize > 0 ? explode("\n", $result['headers']) : [];
                $result['document'] = substr($result['document'], $headerSize);

                // перебираем все заголовки и старые удаляем а добавляем на их основе новые с буквеными ключами
                foreach ($result['headers'] as $key => $row) {
                    $row = trim($row);

                    if ($row === "") {
                        unset($result['headers'][$key]);
                        continue;
                    }

                    $data = explode(":", $row);

                    if (count($data) > 1) {
                        $first = array_shift($data);
                        $first = trim(mb_strtolower($first));
                        $first = str_replace(" ", "-", ucwords(str_replace("-", " ", $first)));

                        $result['headers'][$first] = trim(join(":", $data));
                        unset($result['headers'][$key]);

                        if ($first === 'Content-Type') {
                            $row = explode(";", $result['headers'][$first]);
                            $first = is_string($first = array_shift($row)) ? trim($first) : '';

                            //switch ($first) {
                            //    case 'application/xml':
                            //        $result['headers']['Content-Type'] = 'xml';
                            //        break;
                            //
                            //    case 'application/json':
                            //        $result['headers']['Content-Type'] = 'json';
                            //        break;
                            //
                            //    default:
                            //        $result['headers']['Content-Type'] = $first;
                            //}

                            $second = is_string($second = array_shift($row)) ? trim($second) : '';
                            $result['headers']['Content-Charset'] = str_replace('charset=', '', $second);
                        }

                    } elseif (preg_match("/^HTTP\//is", $row)) {
                        $str = preg_replace('/[^0-9\s]/isu', '', $row);
                        $match = explode(" ", $str);
                        $code = isset($match[0]) && (int)$match[0] >= 100
                            ? $match[0]
                            : (isset($match[1])
                            && (int)$match[1]
                            >= 100 ? $match[1] : trim($row));
                        $version = isset($match[0]) && (int)$match[0] < 100 ? trim($match[0]) : substr($row, 0, 3);
                        $result['headers']['HTTP'] = empty($code) ? trim($row) : trim($code);
                        $result['headers']['Http-Version'] = $version;
                        unset($result['headers'][$key]);
                    }
                }
            }

            return new AppSimpleResponse($result);

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * HTTP-авторизация.
     *
     * @param string $user
     * @param string $password
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    protected function setHttpAuth($user, $password): void
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
    #[\ReturnTypeWillChange]
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
    protected function setFile($path = null, $name = null, $mimeType = 'application/octet-stream'): void
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
    #[\ReturnTypeWillChange]
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
    protected function setCookies($cookies): void
    {
        if (is_array($cookies)) {
            $this->cookies = array_merge($this->cookies, $cookies);
        }
    }

    /**
     * Передать указанный массив как поток данных в формате JSON.
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array | string $data
     * @param string         $uri
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function streamJson($data, $uri = null)
    {
        $this->setStreamJson($data, $uri);

        return $this;
    }

    /**
     * Передать указанный массив как поток данных в формате JSON.
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array | string $data
     * @param string         $uri
     * @return void
     */
    protected function setStreamJson($data, $uri = null): void
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
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array | string $data
     * @param string         $uri
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function streamXml($data, $uri = null)
    {
        $this->setStreamXml($data, $uri);

        return $this;
    }

    /**
     * Передать указанный массив как поток данных в формате XML.
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array | string $data
     * @param null           $uri
     * @param string         $rootNode
     * @return void
     */
    protected function setStreamXml($data, $uri = null, $rootNode = 'root'): void
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
    #[\ReturnTypeWillChange]
    public function method($str)
    {
        $this->setMethod($str);

        return $this;
    }

    /**
     * Устанавливает метод был использования в запросе.
     *
     * @param string $str
     * @return void
     */
    protected function setMethod($str): void
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
    #[\ReturnTypeWillChange]
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
    protected function setUrl($str): void
    {
        $this->url = $this->options[CURLOPT_URL] = (string)$str;
        $this->settingsForURI($str);
    }

    /**
     * Возвращает URI текущего сеанса.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $uri
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function header($headers)
    {
        $this->setHeader($headers);

        return $this;
    }

    /**
     * @param string|array $headers
     * @return void
     */
    protected function setHeader($headers): void
    {
        if (is_array($headers)) {
            foreach ($headers as $row) {
                $this->headers[md5($row)] = $row;
            }
        }

        if (is_string($headers)) {
            $this->headers[md5($headers)] = $headers;
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
    #[\ReturnTypeWillChange]
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
    protected function setReturnTransfer($flag): void
    {
        $this->options[CURLOPT_RETURNTRANSFER] = (boolean)$flag;
    }

    /**
     * Включение\выключение заголовков в выводе результата запроса.
     *
     * @param boolean $flag
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    protected function setHeadersInOutput($flag): void
    {
        $this->options[CURLOPT_HEADER] = $this->headerInResponse = (boolean)$flag;
    }

    /**
     * Определение следования любому заголовку "Location" в ответе.
     *
     * @param boolean $flag
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    protected function setFollowsAnyHeader($flag): void
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
    #[\ReturnTypeWillChange]
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
    protected function setAcceptEncoding($encoding): void
    {
        $this->options[CURLOPT_ENCODING] = (string)$encoding;
    }

    /**
     * Задает значение HTTP заголовка: User-Agent:...
     *
     * @param string $userAgent
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    protected function setUserAgent($userAgent): void
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
    #[\ReturnTypeWillChange]
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
    protected function setAccept($str): void
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
    #[\ReturnTypeWillChange]
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
    protected function setAutoReferer($flag): void
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
    #[\ReturnTypeWillChange]
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
    protected function setConnectTimeout($seconds): void
    {
        $this->options[CURLOPT_CONNECTTIMEOUT] = (int)$seconds;
    }

    /**
     * Устанавливает таймаут ответа в секундах, которое отводятся для работы CURL-функций.
     *
     * @param integer $seconds
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    protected function setTimeout($seconds): void
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
    #[\ReturnTypeWillChange]
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
    protected function setMaxRedirect($num): void
    {
        $this->options[CURLOPT_MAXREDIRS] = (int)$num;
    }

    /**
     * Устанавливает флаг начать новую "сессию" cookies.
     *
     * @param boolean $flag
     * @return $this
     */
    #[\ReturnTypeWillChange]
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
    protected function setNewSession(bool $flag): void
    {
        $this->options[CURLOPT_COOKIESESSION] = (boolean)$flag;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного.
     *
     * @param boolean $flag
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function freshConnect(bool $flag)
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
    protected function setFreshConnect(bool $flag): void
    {
        $this->options[CURLOPT_FRESH_CONNECT] = (boolean)$flag;
    }

    /**
     * Устанавливает флаг принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно
     *
     * @param boolean $flag
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function forbidReUse(bool $flag)
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
    protected function setForbidReUse(bool $flag): void
    {
        $this->options[CURLOPT_FORBID_REUSE] = (boolean)$flag;
    }

    /**
     * Устанавливает название специального метода, который будет использован в HTTP запросе вместо GET или HEAD.
     *
     * @param string $name
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function customRequest(string $name)
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
    protected function setCustomRequest(string $name): void
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
    #[\ReturnTypeWillChange]
    public function sslValidation(bool $flag)
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
    protected function setSslValidation(bool $flag): void
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
    #[\ReturnTypeWillChange]
    public function sslValidationHost(int $num)
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
    protected function setSslValidationHost(int $num): void
    {
        $this->options[CURLOPT_SSL_VERIFYHOST] = (int)$num;
    }

    /**
     * Установка флага включения или выключения SSL сертификата урла если тот https
     *
     * @param boolean $flag
     * @return $this
     */
    #[\ReturnTypeWillChange]
    public function sslChecks(bool $flag)
    {
        $this->setSslValidationHost($flag);

        return $this;
    }

    /**
     * @param bool $flag
     * @return void
     */
    protected function setSslChecks(bool $flag): void
    {
        $this->sslChecks = (boolean)$flag;
    }
}