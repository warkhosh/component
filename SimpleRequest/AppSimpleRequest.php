<?php

namespace Warkhosh\Component\SimpleRequest;

use CURLFile;
use DOMDocument;
use SimpleXMLElement;
use Throwable;
use Warkhosh\Component\Config\AppConfig;

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
    protected string $url = '';

    /**
     * @var string
     */
    protected string $method = "GET";

    /**
     * Содержит тип потоковой передачи со значением контента
     *
     * @var array
     */
    protected array $stream = [];

    /**
     * Флаг получения заголовков в ответе
     *
     * @var bool
     */
    protected bool $headerInResponse = false;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $cookies = [];

    /**
     * @var bool
     */
    protected bool $sslChecks = false;

    /**
     * Поля со значениями которые указали для передачи.
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * Файлы, которые указали для передачи.
     *
     * @var array
     */
    protected array $files = [];

    /**
     * @var array
     */
    protected array $accept = [];

    /**
     * Результат выполнения.
     *
     * @var array
     */
    protected array $result = [
        'errno' => 0,
        'error' => '',
        'document' => '',
        'stream' => null,
        'headers' => [],
        'http_code' => 0,
    ];

    /**
     * AppSimpleRequest constructor
     *
     * @throws Throwable
     */
    public function __construct()
    {
        $this->initDefault();
    }

    /**
     * @return static
     */
    public static function init(): static
    {
        return new static();
    }

    /**
     * Настройки приложения для большинства запросов
     *
     * @return static
     * @throws Throwable
     */
    public function initDefault(): static
    {
        $this->url = '';
        $appConfig = AppConfig::getInstance();

        $this->method = $appConfig->get("spider.setting.default.method", "GET");
        $this->headers = $this->options = $this->cookies = $this->fields = $this->files = $this->accept = [];
        $this->result = ['errno' => 0, 'error' => '', 'document' => '', 'headers' => [], 'http_code' => 0];
        $this->setSslChecks($appConfig->get("spider.setting.default.ssl_checks", false));

        $this->setReturnTransfer($appConfig->get("spider.setting.default.return_transfer", true));      // return web page
        $this->setHeadersInOutput($appConfig->get("spider.setting.default.headers_in_output", true));   // return headers
        $this->setFollowsAnyHeader($appConfig->get("spider.setting.default.follows_any_header", true)); // follow redirects
        $this->setAcceptEncoding($appConfig->get("spider.setting.default.accept_encoding", ""));        // handle all encoding
        $this->setAutoReferer($appConfig->get("spider.setting.default.auto_referer", true));        // set referer on redirect
        $this->setConnectTimeout($appConfig->get("spider.setting.default.connect_timeout", 10));    // timeout on connect
        $this->setTimeout($appConfig->get("spider.setting.default.timeout", 120));                  // timeout on response
        $this->setMaxRedirect($appConfig->get("spider.setting.default.max_redirect", 10));          // stop after 10 redirects
        $this->setFreshConnect($appConfig->get("spider.setting.default.fresh_connect", true));
        $this->setForbidReUse($appConfig->get("spider.setting.default.forbid_re_use", true));

        if (is_array($headers = $appConfig->get("spider.setting.default.headers", null))) {
            $this->setHeader($headers);
        }

        if (! is_null($userAgent = $appConfig->get("spider.setting.default.user_agent", null))) {
            $this->setUserAgent($userAgent);
        }

        return $this;
    }

    /**
     * @param string|null $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    public function get(string $uri = null): AppSimpleResponse
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        $this->setMethod("GET");

        return $this->request();
    }

    /**
     * @param array $fields
     * @param string|null $uri
     * @param string|null $referer
     * @return AppSimpleResponse
     * @throws Throwable
     */
    public function post(array $fields = [], ?string $uri = null, ?string $referer = null): AppSimpleResponse
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
     * @param array $fields
     * @param string|null $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    public function put(array $fields = [], ?string $uri = null): AppSimpleResponse
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
     * @param array $fields
     * @param string|null $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    public function patch(array $fields = [], ?string $uri = null): AppSimpleResponse
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
     * @param array $fields
     * @param string|null $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    public function delete(array $fields = [], ?string $uri = null): AppSimpleResponse
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
     * @param string|null $uri
     * @return AppSimpleResponse
     * @throws Throwable
     */
    public function head(?string $uri = null): AppSimpleResponse
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
     * @param array $fields
     * @param string|null $uri
     * @return static
     */
    public function fields(array $fields = [], ?string $uri = null): static
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
    public function request(): AppSimpleResponse
    {
        try {
            $ch = curl_init();

            // Если указали передачу файла, но метод не POST, меняем его!
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
                $this->setHeader("Accept: ".trim(join(", ", $this->accept)));
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

                // перебираем все заголовки и старые удаляем, а добавляем на их основе новые с буквенными ключами
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
                            //$first = is_string($first = array_shift($row)) ? trim($first) : '';

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
     * HTTP-авторизация
     *
     * @param string $user
     * @param string $password
     * @return static
     */
    public function httpAuth(string $user, string $password): static
    {
        $this->setHttpAuth($user, $password);

        return $this;
    }

    /**
     * HTTP-авторизация
     *
     * @param string $user
     * @param string $password
     * @return void
     */
    protected function setHttpAuth(string $user, string $password): void
    {
        $encodedAuth = base64_encode($user.":".$password);

        $this->setHeader("Authentication : Basic ".$encodedAuth);
        $this->options[CURLOPT_USERPWD] = $user.":".$password;
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
    }

    /**
     * Устанавливает для передачи указанный файл
     *
     * @param string $path - путь до файла на диске
     * @param array|string|null $name - Имя файла в данных для загрузки
     * @param string $mimeType - MIME-тип файла ( по умолчанию это application/octet-stream )
     * @return static
     */
    public function file(
        string $path,
        array|string|null $name = null,
        string $mimeType = 'application/octet-stream'
    ): static {
        $this->setFile($path, $name, $mimeType);

        return $this;
    }

    /**
     * Устанавливает для передачи указанный файл
     *
     * @param string|null $path путь до файла на диске
     * @param array|string|null $name Имя файла в данных для загрузки
     * @param string $mimeType MIME-тип файла (по умолчанию это application/octet-stream)
     * @return void
     */
    protected function setFile(
        ?string $path = null,
        array|string|null $name = null,
        string $mimeType = 'application/octet-stream'
    ): void {
        if (! is_null($path) && file_exists($path)) {
            if (is_array($name)) {
                $field = array_shift($name);
                $name = (string)array_shift($name);
                $field = empty($field) ? "file_".(count($this->files) + 1) : $field;
                $name = empty($name) ? "file_".(count($this->files) + 1) : $name;
                $this->files[$field] = new CURLFile($path, $mimeType, $name);

            } else {
                $name = is_null($name) ? "file_".(count($this->files) + 1) : $name;
                $this->files[$name] = new CURLFile($path, $mimeType, $name);
            }
        }
    }

    /**
     * Устанавливает содержимое заголовка "Cookie", который будет отправлен с HTTP запросом
     *
     * @param array $cookies
     * @return static
     */
    public function cookies(array $cookies): static
    {
        $this->setCookies($cookies);

        return $this;
    }

    /**
     * Устанавливает содержимое заголовка "Cookie", который будет отправлен с HTTP запросом
     *
     * @param array $cookies
     * @return void
     */
    protected function setCookies(array $cookies): void
    {
        $this->cookies = array_merge($this->cookies, $cookies);
    }

    /**
     * Передать указанный массив как поток данных в формате JSON
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array|string $data
     * @param string|null $uri
     * @return static
     */
    public function streamJson(array|string $data, ?string $uri = null): static
    {
        $this->setStreamJson($data, $uri);

        return $this;
    }

    /**
     * Передать указанный массив как поток данных в формате JSON
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array|string $data
     * @param string|null $uri
     * @return void
     */
    protected function setStreamJson(array|string $data, ?string $uri = null): void
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        $this->files = $this->fields = [];
        $this->stream = ["json", $data];

        $this->setMethod("POST");
        $this->setCustomRequest("POST");
        $this->setReturnTransfer(true);
        $this->setHeader(['Content-Type: application/json', 'Content-Length: '.strlen($data)]);
        $this->accept('application/json');
    }

    /**
     * Передать указанный массив как поток данных в формате XML
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array|string $data
     * @param string|null $uri
     * @return static
     */
    public function streamXml(array|string $data, ?string $uri = null): static
    {
        $this->setStreamXml($data, $uri);

        return $this;
    }

    /**
     * Передать указанный массив как поток данных в формате XML
     *
     * @note метод устанавливает дополнительные параметры для передачи!
     *
     * @param array|string $data
     * @param string|null $uri
     * @param string $rootNode
     * @return void
     */
    protected function setStreamXml(array|string $data, ?string $uri = null, string $rootNode = 'root'): void
    {
        if (! is_null($uri)) {
            $this->setUrl($uri);
        }

        if (is_array($data)) {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML(array_to_xml($data, new SimpleXMLElement("<{$rootNode}/>"))->asXML());
            $dom->formatOutput = true;
            $data = $dom->saveXml();
        }

        $this->files = $this->fields = [];
        $this->stream = ["xml", $data];

        $this->setMethod("POST");
        $this->setCustomRequest("POST");
        $this->setReturnTransfer(true);
        $this->setHeader(['Content-Type: application/xml', 'Content-Length: '.strlen($data)]);
        $this->accept('application/xml');
    }

    /**
     * Устанавливает метод был использования в запросе
     *
     * @param string $str
     * @return static
     */
    public function method(string $str): static
    {
        $this->setMethod($str);

        return $this;
    }

    /**
     * Устанавливает метод был использования в запросе
     *
     * @param string $str
     * @return void
     */
    protected function setMethod(string $str): void
    {
        $this->method = in_array($str, ["GET", "POST", "PUT", "PATCH", "DELETE"]) ? $str : "GET";
    }

    /**
     * Устанавливает URI для текущего сеанса
     *
     * @param string $str
     * @return static
     */
    public function url(string $str): static
    {
        $this->setUrl($str);

        return $this;
    }

    /**
     * Устанавливает URI для текущего сеанса
     *
     * @param string $str
     * @return void
     */
    protected function setUrl(string $str): void
    {
        $this->url = $this->options[CURLOPT_URL] = $str;
        $this->settingsForURI($str);
    }

    /**
     * Возвращает URI текущего сеанса
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $uri
     * @return static
     */
    protected function settingsForURI(string $uri): static
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
     * @param array|string $headers
     * @return static
     */
    public function header(array|string $headers): static
    {
        $this->setHeader($headers);

        return $this;
    }

    /**
     * @param array|string $headers
     * @return void
     */
    protected function setHeader(array|string $headers): void
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
     * @note если указать false, то результат будет напрямую выведен в браузер
     *
     * @param bool $flag
     * @return static
     */
    public function returnTransfer(bool $flag): static
    {
        $this->setReturnTransfer($flag);

        return $this;
    }

    /**
     * Установка флага как отдать результат передачи в качестве строки из curl_exec()
     *
     * @note если указать false, то результат будет напрямую выведен в браузер
     *
     * @param bool $flag
     * @return void
     */
    protected function setReturnTransfer(bool $flag): void
    {
        $this->options[CURLOPT_RETURNTRANSFER] = $flag;
    }

    /**
     * Включение\выключение заголовков в выводе результата запроса
     *
     * @param bool $flag
     * @return static
     */
    public function headersInOutput(bool $flag): static
    {
        $this->setHeadersInOutput($flag);

        return $this;
    }

    /**
     * Включение\выключение заголовков в выводе результата запроса
     *
     * @param bool $flag
     * @return void
     */
    protected function setHeadersInOutput(bool $flag): void
    {
        $this->options[CURLOPT_HEADER] = $this->headerInResponse = $flag;
    }

    /**
     * Определение следования любому заголовку "Location" в ответе
     *
     * @param bool $flag
     * @return static
     */
    public function followsAnyHeader(bool $flag): static
    {
        $this->setFollowsAnyHeader($flag);

        return $this;
    }

    /**
     * Определение следования любому заголовку "Location" в ответе
     *
     * @param bool $flag
     * @return void
     */
    protected function setFollowsAnyHeader(bool $flag): void
    {
        $this->options[CURLOPT_FOLLOWLOCATION] = $flag;
    }

    /**
     * Включает декодирование запроса: Accept-Encoding:...
     *
     * @note поддерживаемыми кодировками являются "identity", "deflate" и "gzip".
     *
     * @param string $encoding
     * @return static
     */
    public function acceptEncoding(string $encoding): static
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
    protected function setAcceptEncoding(string $encoding): void
    {
        $this->options[CURLOPT_ENCODING] = $encoding;
    }

    /**
     * Задает значение HTTP заголовка: User-Agent:...
     *
     * @param string $userAgent
     * @return static
     */
    public function userAgent(string $userAgent): static
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
    protected function setUserAgent(string $userAgent): void
    {
        $this->options[CURLOPT_USERAGENT] = $userAgent;
        $this->setHeader("User-Agent: {$userAgent}");
    }

    /**
     * Задает MIME тип.
     *
     * @param array|string $str
     * @return static
     */
    public function accept(array|string $str): static
    {
        $this->setAccept($str);

        return $this;
    }

    /**
     * Задает MIME тип
     *
     * @param array|string $str
     * @return void
     */
    protected function setAccept(array|string $str): void
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
     * Включает\выключает установку поля Referer: в перенаправленных запросах
     *
     * @param bool $flag
     * @return static
     */
    public function autoReferer(bool $flag): static
    {
        $this->setAutoReferer($flag);

        return $this;
    }

    /**
     * Включает\выключает установку поля Referer: в перенаправленных запросах
     *
     * @param bool $flag
     * @return void
     */
    protected function setAutoReferer(bool $flag): void
    {
        $this->options[CURLOPT_AUTOREFERER] = $flag;
    }

    /**
     * Устанавливает таймаут соединения в секундах, которые ожидаются при попытке подключения
     *
     * @note используйте 0, чтобы ждать бесконечно
     *
     * @param int $seconds
     * @return static
     */
    public function connectTimeout(int $seconds): static
    {
        $this->setConnectTimeout($seconds);

        return $this;
    }

    /**
     * Устанавливает таймаут соединения в секундах, которые ожидаются при попытке подключения
     *
     * @note используйте 0, чтобы ждать бесконечно
     *
     * @param int $seconds
     * @return void
     */
    protected function setConnectTimeout(int $seconds): void
    {
        $this->options[CURLOPT_CONNECTTIMEOUT] = $seconds;
    }

    /**
     * Устанавливает таймаут ответа в секундах, которое отводятся для работы CURL-функций
     *
     * @param int $seconds
     * @return static
     */
    public function timeout(int $seconds): static
    {
        $this->setTimeout($seconds);

        return $this;
    }

    /**
     * Устанавливает таймаут ответа в секундах, которое отводятся для работы CURL-функций
     *
     * @param int $seconds
     * @return void
     */
    protected function setTimeout(int $seconds): void
    {
        $this->options[CURLOPT_TIMEOUT] = $seconds;
    }

    /**
     * Устанавливает максимальное количество принимаемых редиректов
     *
     * @note Используйте этот параметр вместе с параметром CURLOPT_FOLLOWLOCATION
     *
     * @param int $num
     * @return static
     */
    public function maxRedirect(int $num): static
    {
        $this->setMaxRedirect($num);

        return $this;
    }

    /**
     * Устанавливает максимальное количество принимаемых редиректов
     *
     * @note Используйте этот параметр вместе с параметром CURLOPT_FOLLOWLOCATION
     *
     * @param int $num
     * @return void
     */
    protected function setMaxRedirect(int $num): void
    {
        $this->options[CURLOPT_MAXREDIRS] = $num;
    }

    /**
     * Устанавливает флаг начать новую "сессию" cookies
     *
     * @param bool $flag
     * @return static
     */
    public function newSession(bool $flag): static
    {
        $this->setNewSession($flag);

        return $this;
    }

    /**
     * Устанавливает флаг начать новую "сессию" cookies
     *
     * @param bool $flag
     * @return void
     */
    protected function setNewSession(bool $flag): void
    {
        $this->options[CURLOPT_COOKIESESSION] = $flag;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного
     *
     * @param bool $flag
     * @return static
     */
    public function freshConnect(bool $flag): static
    {
        $this->setFreshConnect($flag);

        return $this;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного
     *
     * @param bool $flag
     * @return void
     */
    protected function setFreshConnect(bool $flag): void
    {
        $this->options[CURLOPT_FRESH_CONNECT] = $flag;
    }

    /**
     * Устанавливает флаг принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно
     *
     * @param bool $flag
     * @return static
     */
    public function forbidReUse(bool $flag): static
    {
        $this->setForbidReUse($flag);

        return $this;
    }

    /**
     * Устанавливает флаг принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно
     *
     * @param bool $flag
     * @return void
     */
    protected function setForbidReUse(bool $flag): void
    {
        $this->options[CURLOPT_FORBID_REUSE] = $flag;
    }

    /**
     * Устанавливает название специального метода, который будет использован в HTTP запросе вместо GET или HEAD
     *
     * @param string $name
     * @return static
     */
    public function customRequest(string $name): static
    {
        $this->setCustomRequest($name);

        return $this;
    }

    /**
     * Устанавливает название специального метода, который будет использован в HTTP запросе вместо GET или HEAD
     *
     * @param string $name
     * @return void
     */
    protected function setCustomRequest(string $name): void
    {
        $this->options[CURLOPT_CUSTOMREQUEST] = strtoupper($name);
    }

    /**
     * Устанавливает флаг проверки сертификата удаленного сервера
     *
     * @note начиная с curl 7.10, по умолчанию этот параметр имеет значение TRUE
     * @note если CURLOPT_SSL_VERIFYPEER установлен в FALSE, возможно потребуется установить CURLOPT_SSL_VERIFYHOST в TRUE или FALSE
     *
     * @param bool $flag
     * @return static
     */
    public function sslValidation(bool $flag): static
    {
        $this->setSslValidation($flag);

        return $this;
    }

    /**
     * Устанавливает флаг принудительного использования нового соединения вместо закешированного
     *
     * @param bool $flag
     * @return void
     */
    protected function setSslValidation(bool $flag): void
    {
        $this->options[CURLOPT_SSL_VERIFYPEER] = $flag;
    }

    /**
     * Устанавливает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения
     *
     * @note значение 1 означает проверку существования имени, значение 2 - кроме того, и проверку соответствия имени хоста
     *
     * @param int $num
     * @return static
     */
    public function sslValidationHost(int $num): static
    {
        $this->setSslValidationHost($num);

        return $this;
    }

    /**
     * Устанавливает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения
     *
     * @note значение 1 означает проверку существования имени, значение 2 - кроме того, и проверку соответствия имени хоста
     *
     * @param int $num
     * @return void
     */
    protected function setSslValidationHost(int $num): void
    {
        $this->options[CURLOPT_SSL_VERIFYHOST] = $num;
    }

    /**
     * Установка флага включения или выключения SSL сертификата урла если тот https
     *
     * @param bool $flag
     * @return static
     */
    public function sslChecks(bool $flag): static
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
        $this->sslChecks = $flag;
    }
}
