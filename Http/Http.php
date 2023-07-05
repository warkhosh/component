<?php

namespace Warkhosh\Component\Http;

use Warkhosh\Component\Http\Exception\HttpException;

class Http
{
    /**
     * Список кодов состояния HTTP
     * https://ru.wikipedia.org/wiki/Список_кодов_состояния_HTTP
     */
    const HTTP_100_CONTINUE = 100;
    const HTTP_101_SWITCHING_PROTOCOLS = 101;
    const HTTP_200_OK = 200;
    const HTTP_201_CREATED = 201;
    const HTTP_202_ACCEPTED = 202;
    const HTTP_203_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_204_NO_CONTENT = 204;
    const HTTP_205_RESET_CONTENT = 205;
    const HTTP_206_PARTIAlL_CONTENT = 206;

    const HTTP_300_MULTIPLE_CHOICES = 300;
    const HTTP_301_MOVED_PERMANENTLY = 301;
    const HTTP_302_MOVED_TEMPORARILY = 302;
    const HTTP_303_SEE_OTHER = 303;
    const HTTP_304_NOT_MODIFIED = 304;
    const HTTP_305_USE_PROXY = 305;

    const HTTP_400_BAD_REQUEST = 400;
    const HTTP_401_UNAUTHORIZED = 401;
    const HTTP_402_PAYMENT_REQUIRED = 402;
    const HTTP_403_FORBIDDEN = 403;
    const HTTP_404_NOT_FOUND = 404;
    const HTTP_405_METHOD_NOT_ALLOWED = 405;
    const HTTP_406_NOT_ACCEPTABLE = 406;
    const HTTP_407_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_408_REQUEST_TIME_OUT = 408;
    const HTTP_409_CONFLICT = 409;
    const HTTP_410_GONE = 410;
    const HTTP_411_LENGTH_REQUIRED = 411;
    const HTTP_412_PRECONDITION_FAILED = 412;
    const HTTP_413_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_414_REQUEST_URI_TOO_LARGE = 414;
    const HTTP_415_UNSUPPORTED_MEDIA_TYPE = 415;

    const HTTP_500_INTERNAL_SERVER_ERROR = 500;
    const HTTP_501_NOT_IMPLEMENTED = 501;
    const HTTP_502_BAD_GATEWAY = 502;
    const HTTP_503_SERVICE_UNAVAILABLE = 503;
    const HTTP_504_GATEWAY_TIME_OUT = 504;
    const HTTP_505_HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Устанавливает по полученному коду заголовок
     *
     * @param int $code
     * @return void
     * @throws HttpException
     */
    function setHttpHeader(int $code)
    {
        switch ($code) {
            // 200
            case self::HTTP_100_CONTINUE:
                $text = 'Continue';
                break;

            case self::HTTP_101_SWITCHING_PROTOCOLS:
                $text = 'Switching Protocols';
                break;

            case self::HTTP_200_OK:
                $text = 'OK';
                break;

            case self::HTTP_201_CREATED:
                $text = 'Created';
                break;

            case self::HTTP_202_ACCEPTED:
                $text = 'Accepted';
                break;

            case self::HTTP_203_NON_AUTHORITATIVE_INFORMATION:
                $text = 'Non-Authoritative Information';
                break;

            case self::HTTP_204_NO_CONTENT:
                $text = 'No Content';
                break;

            case self::HTTP_205_RESET_CONTENT:
                $text = 'Reset Content';
                break;

            case self::HTTP_206_PARTIAlL_CONTENT:
                $text = 'Partial Content';
                break;

            // 300

            case self::HTTP_300_MULTIPLE_CHOICES:
                $text = 'Multiple Choices';
                break;

            case self::HTTP_301_MOVED_PERMANENTLY:
                $text = 'Moved Permanently';
                break;

            case self::HTTP_302_MOVED_TEMPORARILY:
                $text = 'Moved Temporarily';
                break;

            case self::HTTP_303_SEE_OTHER:
                $text = 'See Other';
                break;

            case self::HTTP_304_NOT_MODIFIED:
                $text = 'Not Modified';
                break;

            case self::HTTP_305_USE_PROXY:
                $text = 'Use Proxy';
                break;

            // 400

            case self::HTTP_400_BAD_REQUEST:
                $text = 'Bad Request';
                break;

            case self::HTTP_401_UNAUTHORIZED:
                $text = 'Unauthorized';
                break;

            case self::HTTP_402_PAYMENT_REQUIRED:
                $text = 'Payment Required';
                break;

            case self::HTTP_403_FORBIDDEN:
                $text = 'Forbidden';
                break;

            case self::HTTP_404_NOT_FOUND:
                $text = 'Not Found';
                break;

            case self::HTTP_405_METHOD_NOT_ALLOWED:
                $text = 'Method Not Allowed';
                break;

            case self::HTTP_406_NOT_ACCEPTABLE:
                $text = 'Not Acceptable';
                break;

            case self::HTTP_407_PROXY_AUTHENTICATION_REQUIRED:
                $text = 'Proxy Authentication Required';
                break;

            case self::HTTP_408_REQUEST_TIME_OUT:
                $text = 'Request Time-out';
                break;

            case self::HTTP_409_CONFLICT:
                $text = 'Conflict';
                break;

            case self::HTTP_410_GONE:
                $text = 'Gone';
                break;

            case self::HTTP_411_LENGTH_REQUIRED:
                $text = 'Length Required';
                break;

            case self::HTTP_412_PRECONDITION_FAILED:
                $text = 'Precondition Failed';
                break;

            case self::HTTP_413_REQUEST_ENTITY_TOO_LARGE:
                $text = 'Request Entity Too Large';
                break;

            case self::HTTP_414_REQUEST_URI_TOO_LARGE:
                $text = 'Request-URI Too Large';
                break;

            case self::HTTP_415_UNSUPPORTED_MEDIA_TYPE:
                $text = 'Unsupported Media Type';
                break;

            // 500

            case self::HTTP_500_INTERNAL_SERVER_ERROR:
                $text = 'Internal Server Error';
                break;

            case self::HTTP_501_NOT_IMPLEMENTED:
                $text = 'Not Implemented';
                break;

            case self::HTTP_502_BAD_GATEWAY:
                $text = 'Bad Gateway';
                break;

            case self::HTTP_503_SERVICE_UNAVAILABLE:
                $text = 'Service Unavailable';
                break;

            case self::HTTP_504_GATEWAY_TIME_OUT:
                $text = 'Gateway Time-out';
                break;

            case self::HTTP_505_HTTP_VERSION_NOT_SUPPORTED:
                $text = 'HTTP Version not supported';
                break;

            default:
                throw new HttpException("Unknown http status code {$code}");
        }

        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

        header("{$protocol} {$code} {$text}");
        http_response_code($code);
    }

    /**
     * Возвращает код заданного HTTP заголовка
     *
     * @note не в окружении веб-сервера (например, в CLI), будет возвращено false
     * @return int|false
     */
    function getHttpCode()
    {
        return http_response_code();
    }
}