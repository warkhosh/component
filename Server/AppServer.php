<?php

namespace Warkhosh\Component\Server;

use Warkhosh\Component\Url\UrlHelper;
use Warkhosh\Singleton\Trait\Singleton;
use Exception;

/**
 * Class AppServer
 *
 * Класс для работы с переменной $_SERVER с расширенным функционалом по её значениям
 *
 * @version 2.0
 *
 * @property array referer_queries     - список query параметров из referer!
 * @property string referer_query      - строка с query параметрами из referer!
 * @property string referer_path       - путь из referer без файла и query параметров!
 * @property string referer_file       - только название файла (если есть) из referer!
 * @property string referer_uri        - пути и query параметрами из referer без протокола и домена!
 * @property array request_queries     - список query параметров текущего запроса!
 * @property string request_query      - значение query параметров у текущего запроса!
 * @property string request_path       - только путь текущего запроса без файла и query параметров!
 * @property string request_first_path - только первая папка из пути у текущего запроса!
 * @property array request_paths       - список папок у текущего пути в запросе!
 * @property string request_file       - только название файла (если есть) из текущего запроса!
 * @property string request_uri        - путь с query параметрами текущего запроса
 * @property string user_agent
 * @property boolean has_user_agent    - флаг наличия агента в запросе к нам
 * @property string referer            - путь + файл + query параметры из referer
 * @property string client_ip
 * @property string server_ip
 * @property string remote_addr
 * @property string request_scheme
 * @property string protocol
 * @property string name
 * @property string port
 * @property string host               - протокол + домен
 * @property string method
 * @property string request_method
 * @property int http_response_code - код ответа HTTP
 */
class AppServer
{
    use Singleton;

    private array $property = [];

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get(string $name): mixed
    {
        $permitted = [
            'referer_queries',
            'referer_query',
            'referer_path',
            'referer_file',
            'referer_uri',
            'request_queries',
            'request_query',
            'request_uri',
            'request_path',
            'request_first_path',
            'request_paths',
            'request_file',
            'user_agent',
            'has_user_agent',
            'referer',
            'client_ip',
            'server_ip',
            'remote_addr',
            'method',
            'request_method',
            'request_scheme',
            'protocol',
            'name',
            'port',
            'host',
            'http_response_code',
        ];

        if (! in_array($name, $permitted)) {
            return null;
        }

        if ($name === 'http_response_code') {
            // не пишем в переменную http_response_code значение, ведь код может измениться от сценария!
            return getHttpResponseCode();
        }

        if ($name === 'referer_queries') {
            if (! key_exists('referer_queries', $this->property)) {
                $this->property['referer_queries'] = UrlHelper::getQueries($this->referer);
            }

            return $this->property['referer_queries'];
        }

        if ($name === 'referer_query') {
            if (! key_exists('referer_query', $this->property)) {
                $this->property['referer_query'] = '';

                if (count($this->referer_queries)) {
                    $this->property['referer_query'] = "?".http_build_query($this->referer_queries);
                }
            }

            return $this->property['referer_query'];
        }

        if ($name === 'referer_path') {
            if (! key_exists('referer_path', $this->property)) {
                $this->property['referer_path'] = ! isEmpty($this->referer)
                    ? UrlHelper::getPath($this->referer)
                    : '';
            }

            return $this->property['referer_path'];
        }

        if ($name === 'referer_file') {
            if (! key_exists('referer_file', $this->property)) {
                $this->property['referer_file'] = UrlHelper::getFile($this->referer);
            }

            return $this->property['referer_file'];
        }

        if ($name === 'referer_uri') {
            if (! key_exists('referer_uri', $this->property)) {
                $this->property['referer_uri'] = $this->referer_path;
                $this->property['referer_uri'] .= ($this->referer_file !== ''
                && mb_substr($this->property['referer_uri'], -1) !== '/' ? "/" : '');
                $this->property['referer_uri'] .= $this->referer_file;
                $this->property['referer_uri'] .= $this->referer_query;
            }

            return $this->property['referer_uri'];
        }

        if ($name === 'request_queries') {
            if (! key_exists('request_queries', $this->property)) {
                $this->property['request_queries'] = UrlHelper::getQueries(
                    UrlHelper::getRequestUri(true)
                );
            }

            return $this->property['request_queries'];
        }

        if ($name === 'request_query') {
            if (! key_exists('request_query', $this->property)) {
                $this->property['request_query'] = '';

                if (count($this->request_queries)) {
                    $this->property['request_query'] = "?".http_build_query($this->request_queries);
                }
            }

            return $this->property['request_query'];
        }

        if ($name === 'request_path') {
            if (! key_exists('request_query', $this->property)) {
                $this->property['request_path'] = UrlHelper::getPath(UrlHelper::getRequestUri(false));
            }

            return $this->property['request_path'];
        }

        if ($name === 'request_paths') {
            if (! key_exists('request_paths', $this->property)) {
                $this->property['request_paths'] = array_values(
                    getExplodeString("/", $this->request_path, [''])
                );
            }

            return $this->property['request_paths'];
        }

        if ($name === 'request_first_path') {
            if (! key_exists('request_first_path', $this->property)) {
                $this->property['request_first_path'] = getFirstValueInArray($this->request_paths);
            }

            return $this->property['request_first_path'];
        }

        if ($name === 'request_file') {
            if (! key_exists('request_first_path', $this->property)) {
                $this->property['request_file'] = UrlHelper::getFile(UrlHelper::getRequestUri(false));
            }

            return $this->property['request_file'];
        }

        if ($name === 'request_uri') {
            if (! key_exists('request_uri', $this->property)) {
                $this->property['request_uri'] = "";

                if ($this->request_path != '') {
                    $this->property['request_uri'] = UrlHelper::getRequestUri(false).$this->request_query;
                }
            }

            return $this->property['request_uri'];
        }

        if ($name === 'request_scheme' || $name === 'protocol') {
            if (! key_exists('protocol', $this->property)) {
                $this->property['protocol'] = UrlHelper::getServerProtocol();
            }

            $this->property['request_scheme'] = $this->property['protocol'];

            return $this->property['protocol'];
        }

        if ($name === 'port') {
            if (! key_exists('port', $this->property)) {
                $this->property['port'] = UrlHelper::getServerPort();
            }

            return $this->property['port'];
        }

        if ($name === 'host') {
            if (! key_exists('host', $this->property)) {
                // @todo пока окончательно не разберусь можно ли использовать SERVER_PORT, порт использовать тут не буду
                $this->property['host'] = UrlHelper::getServerProtocol().UrlHelper::getServerName();
            }

            return $this->property['host'];
        }

        if ($name === 'name') {
            if (! key_exists('name', $this->property)) {
                $this->property['name'] = UrlHelper::getServerName();
            }

            return $this->property['name'];
        }

        if ($name === 'has_user_agent') {
            if (! key_exists('has_user_agent', $this->property)) {
                $this->property['has_user_agent'] = ($this->user_agent !== UrlHelper::USER_AGENT_NOT_DEFINED);
            }

            return $this->property['has_user_agent'];
        }

        if ($name === 'user_agent') {
            if (! key_exists('user_agent', $this->property)) {
                $this->property['user_agent'] = UrlHelper::getUserAgent();
            }

            return $this->property['user_agent'];
        }

        if ($name === 'referer') {
            if (! key_exists('referer', $this->property)) {
                $this->property['referer'] = UrlHelper::getReferer();
            }

            return $this->property['referer'];
        }

        if ($name === 'client_ip' || $name === 'remote_addr') {
            if (! key_exists('client_ip', $this->property)) {
                $this->property['client_ip'] = UrlHelper::getUserIp();
            }

            $this->property['remote_addr'] = $this->property['client_ip'];

            return $this->property['client_ip'];
        }

        if ($name === 'server_ip') {
            if (! key_exists('server_ip', $this->property)) {
                $this->property['server_ip'] = UrlHelper::getServerIp();
            }

            return $this->property['server_ip'];
        }

        if ($name === 'method' || $name === 'request_method') {
            if (! key_exists('request_method', $this->property)) {
                $this->property['request_method'] = UrlHelper::getRequestMethod();
            }

            $this->property['method'] = $this->property['request_method'];

            return $this->property['request_method'];
        }

        return null;
    }

    /**
     * Проверка наличия query значений в параметрах referer (прошлого) запроса
     *
     * @param array|string $name
     * @return bool
     */
    public function hasQueryInReferer(string|array $name): bool
    {
        if (is_string($name) && in_array($name, $this->referer_queries)) {
            return true;

        } elseif (is_array($name) && count($name)) {
            foreach ($name as $key => $value) {
                if (is_string($key)) { // проверка наличия метода и его значения
                    $result = (getFromArray("{$key}", $this->referer_queries) === $value);

                } else {
                    $result = in_array($value, $this->referer_queries); // проверяем только наличие метода
                }

                if (! $result) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Проверка наличия query значений в текущем запросе
     *
     * @param array|string $name
     * @return bool
     */
    public function hasQueryInRequest(string|array $name): bool
    {
        if (is_string($name) && array_key_exists($name, $this->request_queries)) { // проверяем только наличие метода
            return true;

        } elseif (is_array($name) && count($name)) {
            foreach ($name as $key => $value) {
                if (is_string($key)) { // проверка наличия метода и его значения
                    $result = (getFromArray($key, $this->request_queries) === $value);

                } else {
                    $result = in_array($value, $this->referer_queries); // проверяем только наличие метода
                }

                if (! $result) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Модифицирует переданный список query значений
     *
     * @param array $insert список значений для добавления к query параметрам
     * @param array $remove список значений для удаления из query параметров
     * @param array $queries список query параметров
     * @return array
     */
    public function getModifiedQueryList(array $insert = [], array $remove = [], array $queries = []): array
    {
        foreach ($queries as $key => $value) {
            $queries[$key] = getExtractFromArray($remove, $value);
        }

        foreach ($insert as $key => $value) {
            $queries[$key] = $value;
        }

        return $queries;
    }

    /**
     * Формирует список query значений из referer query от переданных параметров
     *
     * @param array $insert список значений для добавления к query параметрам
     * @param array $remove список значений для удаления из query параметров
     * @return array
     */
    public static function getModifyQueryInReferer(array $insert = [], array $remove = []): array
    {
        $queries = static::getInstance()->referer_queries;

        foreach ($queries as $key => $value) {
            $queries[$key] = getExtractFromArray($remove, $value);
        }

        foreach ($insert as $key => $value) {
            $queries[$key] = $value;
        }

        return $queries;
    }

    /**
     * Формирует список query значений в зависимости от переданных параметров
     *
     * @param array $insert список значений для добавления к query параметрам
     * @param array $remove список значений для удаления из query параметров
     * @return array
     */
    public static function getModifyQueryInRequest(array $insert = [], array $remove = []): array
    {
        $queries = static::getInstance()->referer_queries;

        foreach ($queries as $key => $value) {
            $queries[$key] = getExtractFromArray($remove, $value);
        }

        foreach ($insert as $key => $value) {
            $queries[$key] = $value;
        }

        return $queries;
    }
}
