<?php

namespace Warkhosh\Component\Url;

use Warkhosh\Variable\VarArray;
use Warkhosh\Variable\VarStr;
use Exception;

class UrlHelper
{
    /**
     * @var string
     */
    public const USER_AGENT_NOT_DEFINED = 'User agent not defined (undefined)';

    /**
     * @var int
     */
    public const DEFAULT_SERVER_PORT = "80";

    /**
     * Удаление из строки символов которые не допустимы в семантических урлах
     *
     * @param string $str
     * @param string $ignore символы, которые будут проигнорированы в ходе удаления
     * @param bool $toLower признак перевода заглавных букв в строчные
     * @return string
     */
    public static function getRemoveNoSemanticChar(string $str = '', string $ignore = '', bool $toLower = true): string
    {
        $str = rawurldecode(VarStr::trim($str)); // Преобразовывает символьные коды в символ. %20 - станет пробелом
        $str = $toLower ? strtolower($str) : $str;

        return preg_replace("|[^a-zA-Z0-9\_\-".preg_quote($ignore)."]|ium", "", $str);
    }

    /**
     * Возвращает семантически корректный url без левых символов
     *
     * @param array|string $str
     * @param string $ignore символы, которые будут проигнорированы в ходе удаления
     * @return array|string
     */
    public static function getConvertToValid(array|string $str = '', string $ignore = './'): array|string
    {
        if (is_array($str) && count($str)) {
            $return = [];

            foreach ($str as $row) {
                $return[] = static::getRemoveNoSemanticChar($row, $ignore);
            }

            return $return;
        }

        return static::getRemoveNoSemanticChar($str, $ignore);
    }

    /**
     * Разбивает строку на части по слешу.
     * Если $clearBadPath указана как TRUE, будет проверка всех путей и при обнаружении не допустимых символов путь станет пустым
     *
     * @note: метод рассчитан только на работу с REQUEST_URI без данных о домене
     * @note: алгоритм подразумевает корректные ЧПУ без слешей и точек в названии директорий!
     *
     * @param string $str
     * @param bool $clearBadPath
     * @return array
     */
    public static function getPaths(string $str = '', bool $clearBadPath = false): array
    {
        $str = toUTF8($str);
        $str = rawurldecode($str); // Преобразовывает символьные коды в их символы, %20 - станет пробелом

        // Что-бы правильно обрабатывать кривые урлы, левый слеш убираем, а правый оставляем
        $part = VarArray::explode('/', ltrim($str, '/'), '');

        if (count($part) >= 1) {
            // удаляем последнее значение если в нем присутствует точка
            if (VarStr::find('.', VarArray::getLast($part))) {
                array_pop($part);
            }
        }

        if (count($part) && $part = array_values($part)) {
            if ($clearBadPath) {
                foreach ($part as $key => $path) {
                    // не удаляем плохие строчки, поскольку это нарушит логику проверки конкретной секции
                    if ($path != static::getRemoveNoSemanticChar($path, '.')) {
                        $part[$key] = ''; // оставляем тип у значения прежний, а вот данные удаляем
                    }
                }
            }
        }

        return $part;
    }

    /**
     * Возвращает порт на компьютере сервера, используемый веб-сервером для соединения
     *
     * @return string
     */
    public static function getServerPort(): string
    {
        if (isset($_SERVER['CMF_SERVER_PORT'])) {
            return $_SERVER['CMF_SERVER_PORT'];
        }

        return $_SERVER['CMF_SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? self::DEFAULT_SERVER_PORT;
    }

    /**
     * Возвращает имя хоста, на котором выполняется текущий скрипт
     *
     * @return string
     */
    public static function getServerName(): string
    {
        if (isset($_SERVER['CMF_SERVER_NAME'])) {
            return $_SERVER['CMF_SERVER_NAME'];
        }

        return $_SERVER['CMF_SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'ru';
    }

    /**
     * Возвращает протокол с его префиксами для домена
     *
     * @return string
     */
    public static function getServerProtocol(): string
    {
        if ((! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
            || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            || (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) {
            return 'https://';
        }

        return 'http://';
    }

    /**
     * Возвращает путь текущего запроса
     *
     * @param bool $query флаг для включения\выключения query параметров запроса
     * @return string
     * @throws Exception
     */
    public static function getRequestUri(bool $query = true): string
    {
        if ($query && isset($_SERVER['CMF_REQUEST_URI'])) {
            return $_SERVER['CMF_REQUEST_URI'];

        } elseif (! $query && isset($_SERVER['CMF_REQUEST_URI_NO_QUERY'])) {
            return $_SERVER['CMF_REQUEST_URI_NO_QUERY'];
        }

        $requestUri = '';

        if (isset($_SERVER['CMF_REQUEST_URI'])) {
            $requestUri = $_SERVER['CMF_REQUEST_URI'];

        } elseif (array_key_exists('REQUEST_URI', $_SERVER)) {
            $requestUri = (string)$_SERVER['REQUEST_URI'];
            $requestUri = VarStr::getTransformToEncoding($requestUri, "UTF-8");
        }

        $requestUri = trim($requestUri, " \t\n\r\0\x0B");
        $requestUri = trim($requestUri, "\x00..\x1F");

        $_SERVER['REQUEST_URI'] = $_SERVER['CMF_REQUEST_URI'] = str_replace(["\n", "\t", "\r"], "", $requestUri);

        // дополнительные преобразования плохих значений мы уже делаем и пишем в CMF_REQUEST_URI
        if (($uri = ltrim($_SERVER['CMF_REQUEST_URI'], "/")) != $_SERVER['CMF_REQUEST_URI']) {
            $_SERVER['CMF_REQUEST_URI'] = "/{$uri}";
        }

        if (! $query && $requestUri != '') {
            // Обязательно прописываем протокол и сервер иначе два первых слеша будут приняты за протокол!
            $url = UrlHelper::getServerProtocol().UrlHelper::getServerName().$_SERVER['CMF_REQUEST_URI'];
            $_SERVER['CMF_REQUEST_URI_NO_QUERY'] = parse_url($url, PHP_URL_PATH);

            return $_SERVER['CMF_REQUEST_URI_NO_QUERY'];
        }

        return $_SERVER['CMF_REQUEST_URI'];
    }

    /**
     * Возвращает путь без файла и query параметров
     *
     * @note метод следит что-бы значения начинались со слэша
     *
     * @param string|null $uri
     * @return string
     */
    public static function getPath(?string $uri): string
    {
        if ($uri !== "") {
            $uri = parse_url(rawurldecode(VarStr::trim((string)$uri)), PHP_URL_PATH);
            $info = pathinfo($uri);

            if (isset($info['extension'])) {
                $uri = $info['dirname'];
            } else {
                $info['dirname'] = isset($info['dirname']) ? "{$info['dirname']}/" : '';
                $info['dirname'] = $info['dirname'] === '//' ? "/" : $info['dirname'];

                // Данное решение фиксит баг при обрабатке кривого урла, когда в конце get параметров идет слэш или слеши
                // example: http://photogora.ru/background/muslin&filter_category=126/
                $tmp = "{$info['dirname']}{$info['basename']}";
                $uri = rtrim($uri, '/') == $tmp ? $uri : $tmp;
            }
        }

        return VarStr::start("/", (string)$uri);
    }

    /**
     * Возвращает адрес страницы с которой пришли на страницу
     *
     * @return string
     * @throws Exception
     */
    public static function getReferer(): string
    {
        if (isset($_SERVER['CMF_REFERER'])) {
            return $_SERVER['CMF_REFERER'];
        }

        if (isset($_SERVER['HTTP_REFERER']) && mb_strlen($_SERVER['HTTP_REFERER']) > 0) {
            $_SERVER['HTTP_REFERER'] = trim($_SERVER['HTTP_REFERER'], " \t\n\r\0\x0B");
            $_SERVER['HTTP_REFERER'] = trim($_SERVER['HTTP_REFERER'], "\x00..\x1F");

            $_SERVER['CMF_REFERER'] = str_replace(["\n", "\t", "\r"], '', $_SERVER['HTTP_REFERER']);

            // дополнительные преобразования плохих значений мы уже делаем и пишем в CMF_REQUEST_URI
            if (($uri = ltrim($_SERVER['CMF_REFERER'], "/")) != $_SERVER['CMF_REFERER']) {
                $_SERVER['CMF_REFERER'] = "/{$uri}";
            }

            $_SERVER['CMF_REFERER'] = VarStr::getTransformToEncoding($_SERVER['CMF_REFERER'], "UTF-8");

            return $_SERVER['CMF_REFERER'];
        }

        return $_SERVER['CMF_REFERER'] = '';
    }

    /**
     * Возвращает название агента (браузер) через который просматривают сайт
     *
     * @return string
     * @throws Exception
     */
    public static function getUserAgent(): string
    {
        if (isset($_SERVER['CMF_HTTP_USER_AGENT'])) {
            return $_SERVER['CMF_HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT']) && mb_strlen($_SERVER['HTTP_USER_AGENT']) > 0) {
            $_SERVER['HTTP_USER_AGENT'] = trim($_SERVER['HTTP_USER_AGENT'], " \t\n\r\0\x0B");
            $_SERVER['HTTP_USER_AGENT'] = trim($_SERVER['HTTP_USER_AGENT'], "\x00..\x1F");

            $_SERVER['HTTP_USER_AGENT'] = str_replace(["\n", "\t", "\r"], '', $_SERVER['HTTP_USER_AGENT']);
            $_SERVER['CMF_HTTP_USER_AGENT'] = VarStr::getUrlDecode($_SERVER['HTTP_USER_AGENT']);

            $pattern = "/[^a-zA-Zа-яА-ЯйЙёЁ0-9\/\\\,\.\:\;\!\"\'\@\#\$\%\&\*\-\+\_\?\=\|\(\)\[\]\{\}\<\>\s]/";
            $_SERVER['CMF_HTTP_USER_AGENT'] = preg_replace($pattern, "", $_SERVER['CMF_HTTP_USER_AGENT']);

            return $_SERVER['CMF_HTTP_USER_AGENT'];
        }

        return static::USER_AGENT_NOT_DEFINED;
    }

    /**
     * Возвращает строку запроса, если есть
     *
     * @return string
     * @throws Exception
     */
    public static function getQueryString(): string
    {
        if (isset($_SERVER['CMF_QUERY_STRING'])) {
            return $_SERVER['CMF_QUERY_STRING'];
        }

        $_SERVER['CMF_QUERY_STRING'] = UrlHelper::getRequestUri(true);
        $_SERVER['CMF_QUERY_STRING'] = http_build_query(static::getQueries($_SERVER['CMF_QUERY_STRING']));

        return $_SERVER['CMF_QUERY_STRING'];
    }

    /**
     * Возвращает IP посетителя
     *
     * @return string
     */
    public static function getUserIp(): string
    {
        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER) && mb_strlen($_SERVER['HTTP_CLIENT_IP']) > 1) {
            return trim($_SERVER['HTTP_CLIENT_IP']);

        } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)
            && mb_strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 1) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipList = array_map('trim', $ipList);

            return array_shift($ipList);

        } elseif (array_key_exists('HTTP_X_FORWARDED', $_SERVER) && mb_strlen($_SERVER['HTTP_X_FORWARDED']) > 1) {
            return trim($_SERVER['HTTP_X_FORWARDED']);

        } elseif (array_key_exists('HTTP_FORWARDED_FOR', $_SERVER) && mb_strlen($_SERVER['HTTP_FORWARDED_FOR']) > 1) {
            return trim($_SERVER['HTTP_FORWARDED_FOR']);

        } elseif (array_key_exists('HTTP_FORWARDED', $_SERVER) && mb_strlen($_SERVER['HTTP_FORWARDED']) > 1) {
            return trim($_SERVER['HTTP_FORWARDED']);

        } elseif (array_key_exists('HTTP_X_REAL_IP', $_SERVER) && mb_strlen($_SERVER['HTTP_X_REAL_IP']) > 1) {
            return trim($_SERVER['HTTP_X_REAL_IP']);

        } elseif (array_key_exists('REMOTE_ADDR', $_SERVER) && mb_strlen($_SERVER['REMOTE_ADDR']) > 1) {
            return trim($_SERVER['REMOTE_ADDR']);
        }

        return '127.0.0.1';
    }

    /**
     * Возвращает IP-адрес сервера, на котором выполняется текущий скрипт
     *
     * @return string
     */
    public static function getServerIp(): string
    {
        if (isset($_SERVER['CMF_SERVER_ADDR'])) {
            return trim($_SERVER['CMF_SERVER_ADDR']);
        }

        if (array_key_exists('SERVER_ADDR', $_SERVER) && mb_strlen($_SERVER['SERVER_ADDR']) > 1) {
            $_SERVER['CMF_SERVER_ADDR'] = trim($_SERVER['SERVER_ADDR']);

        } elseif (array_key_exists('LOCAL_ADDR', $_SERVER) && mb_strlen($_SERVER['LOCAL_ADDR']) > 1) {
            $_SERVER['CMF_SERVER_ADDR'] = trim($_SERVER['LOCAL_ADDR']);

        } else {
            $_SERVER['CMF_SERVER_ADDR'] = '127.0.0.1';
        }

        return $_SERVER['CMF_SERVER_ADDR'];
    }

    /**
     * Возвращает информацию о файле по указанному пути
     *
     * @note в случае не удачи вернет пустую строку
     *
     * @param string|null $str
     * @return string
     */
    public static function getFile(?string $str): string
    {
        if (is_null($str) || $str === "") {
            return "";
        }

        $str = parse_url(VarStr::trim($str), PHP_URL_PATH);
        $info = pathinfo($str);
        $file = '';

        // если есть расширение файла, то пытаемся отдельно установить параметры файла
        if (isset($info['extension'])
            && isset($info['filename'])
            && ! isEmpty($info['extension'])
            && ! isEmpty($info['filename'])) {
            $file = "{$info['filename']}.{$info['extension']}";
        }

        unset($info);

        return $file;
    }

    /**
     * Возвращает массив query переменных из указанной строки
     *
     * @param string|null $str
     * @return array
     * @throws Exception
     */
    public static function getQueries(?string $str): array
    {
        if (is_null($str) || $str === "") {
            return [];
        }

        $str = getMakeString(VarStr::trim($str));
        $str = VarStr::getUrlDecode($str);

        // если указали ссылку с путями, то выбираем из неё только query параметры
        if (mb_substr($str, 0, 1) === '/' || mb_substr($str, 0, 4) === 'http') {
            $str = ! is_null($tmp = parse_url($str, PHP_URL_QUERY)) ? $tmp : '';
        }

        parse_str(VarStr::getRemoveStart("?", $str), $queries);

        return $queries;
    }

    /**
     * Возвращает название используемого метода для запроса текущей страницы
     *
     * @return string
     * @throws Exception
     */
    public static function getRequestMethod(): string
    {
        static $requestMethod;

        if (! is_null($requestMethod)) {
            return $requestMethod;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $_SERVER['REQUEST_METHOD'] = strtoupper(trim($_SERVER['REQUEST_METHOD']));
            $requestMethod = VarStr::getLower($_SERVER['REQUEST_METHOD']);

            // По общему тренду поддерживаю передачу POST данных с переменной _method
            if ($requestMethod === 'post' && isset($_POST['_method']) && $_POST['_method'] != '') {
                $_POST['_method'] = VarStr::getLower(trim($_POST['_method']));

                if (in_array($_POST['_method'], ['put', 'patch', 'delete'])) {
                    $_SERVER['REQUEST_METHOD'] = strtoupper($requestMethod = $_POST['_method']);
                    unset($_POST['_method']);
                }
            }

            return $requestMethod;
        }

        return $requestMethod = 'undefined';
    }

    /**
     * Генератор урлов
     *
     * @param array $parts
     * @return string
     */
    public static function getGenerated(array $parts = []): string
    {
        $scheme = isset($parts['scheme']) ? VarStr::ending("://", $parts['scheme']) : 'http://';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':'.$parts['pass'] : '';
        $pass = ($user || $pass) ? "{$pass}@" : '';

        if (isEmpty($host)) {
            $scheme = $user = $pass = $host = $port = '';
        }

        $server_name = $parts['server_name'] ?? $scheme.$user.$pass.$host.$port;
        $path = $parts['path'] ?? '';

        if (isset($parts['queries']) && is_array($parts['queries'])) {
            $query = count($parts['queries']) ? "?".http_build_query($parts['queries']) : '';
        } else {
            $query = isset($parts['query']) && ! isEmpty($parts['query']) ? VarStr::start("?", $parts['query']) : '';
        }

        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return "{$server_name}{$path}{$query}{$fragment}";
    }

    /**
     * Переключает в query переменных значения на противоположные
     *
     * @note если значение переключения равно null, то переменная будет просто удалена
     *
     * @param array $queries
     * @param string $query
     * @param array|float|int|string $on
     * @param array|float|int|string|null $off
     * @return array
     */
    public static function getQueryToggle(
        array $queries = [],
        string $query = '',
        array|float|int|string $on = 1,
        array|float|int|string|null $off = null
    ): array {
        if (! is_array($queries)) {
            return [];
        }

        $query = is_array($query) ? $query : [$query];
        $vars = [];

        // если указали массив переменных, то подготавливаем к нему массив значений на основе проверок значений ON и OFF
        if (is_array($query)) {
            foreach ($query as $key => $row) {
                $currentOn = is_array($on) ? (array_key_exists($key, $on) ? $on[$key] : null) : $on;
                $currentOff = is_array($off) ? (array_key_exists($key, $off) ? $off[$key] : null) : $off;

                $vars[$key] = [$currentOn, $currentOff];
            }
        }

        if (is_array($query)) {
            foreach ($query as $key => $name) {
                if (array_key_exists($name, $queries)) {
                    switch ($queries[$name]) {

                        // Переменная равна значению ON
                        case $vars[$key][0]:
                            if (! is_null($vars[$key][1])) { // Есть значение OFF
                                $queries[$name] = $vars[$key][1];
                            } else {
                                unset($queries[$name]);
                            }

                            break;

                            // Переменная равна значению OFF
                        case $vars[$key][1]:
                            if (! is_null($vars[$key][0])) { // Есть значение ON
                                $queries[$name] = $vars[$key][0];
                            } else {
                                unset($queries[$name]);
                            }

                            break;

                        default:
                            unset($queries[$name]);
                    }

                } elseif (! is_null($vars[$key][0])) { // Есть значение ON
                    $queries[$name] = $vars[$key][0];
                }
            }
        }

        return $queries;
    }
}
