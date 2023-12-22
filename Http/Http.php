<?php

namespace Warkhosh\Component\Http;

use Warkhosh\Component\Http\Exception\HttpException;

class Http
{
    /**
     * Список кодов состояния HTTP
     * https://ru.wikipedia.org/wiki/Список_кодов_состояния_HTTP
     */
    const CODE_100_CONTINUE = 100;
    const CODE_101_SWITCHING_PROTOCOLS = 101;
    const CODE_200_OK = 200;
    const CODE_201_CREATED = 201;
    const CODE_202_ACCEPTED = 202;
    const CODE_203_NON_AUTHORITATIVE_INFORMATION = 203;
    const CODE_204_NO_CONTENT = 204;
    const CODE_205_RESET_CONTENT = 205;
    const CODE_206_PARTIAlL_CONTENT = 206;

    const CODE_300_MULTIPLE_CHOICES = 300;
    const CODE_301_MOVED_PERMANENTLY = 301;
    const CODE_302_MOVED_TEMPORARILY = 302;
    const CODE_303_SEE_OTHER = 303;
    const CODE_304_NOT_MODIFIED = 304;
    const CODE_305_USE_PROXY = 305;

    const CODE_400_BAD_REQUEST = 400;
    const CODE_401_UNAUTHORIZED = 401;
    const CODE_402_PAYMENT_REQUIRED = 402;
    const CODE_403_FORBIDDEN = 403;
    const CODE_404_NOT_FOUND = 404;
    const CODE_405_METHOD_NOT_ALLOWED = 405;
    const CODE_406_NOT_ACCEPTABLE = 406;
    const CODE_407_PROXY_AUTHENTICATION_REQUIRED = 407;
    const CODE_408_REQUEST_TIME_OUT = 408;
    const CODE_409_CONFLICT = 409;
    const CODE_410_GONE = 410;
    const CODE_411_LENGTH_REQUIRED = 411;
    const CODE_412_PRECONDITION_FAILED = 412;
    const CODE_413_REQUEST_ENTITY_TOO_LARGE = 413;
    const CODE_414_REQUEST_URI_TOO_LARGE = 414;
    const CODE_415_UNSUPPORTED_MEDIA_TYPE = 415;

    const CODE_500_INTERNAL_SERVER_ERROR = 500;
    const CODE_501_NOT_IMPLEMENTED = 501;
    const CODE_502_BAD_GATEWAY = 502;
    const CODE_503_SERVICE_UNAVAILABLE = 503;
    const CODE_504_GATEWAY_TIME_OUT = 504;
    const CODE_505_HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Устанавливает по полученному коду заголовок
     *
     * @param int $code
     * @return void
     * @throws HttpException
     */
    static public function setHttpHeader(int $code): void
    {
        switch ($code) {
            // 200
            case self::CODE_100_CONTINUE:
                $text = 'Continue';
                break;

            case self::CODE_101_SWITCHING_PROTOCOLS:
                $text = 'Switching Protocols';
                break;

            case self::CODE_200_OK:
                $text = 'OK';
                break;

            case self::CODE_201_CREATED:
                $text = 'Created';
                break;

            case self::CODE_202_ACCEPTED:
                $text = 'Accepted';
                break;

            case self::CODE_203_NON_AUTHORITATIVE_INFORMATION:
                $text = 'Non-Authoritative Information';
                break;

            case self::CODE_204_NO_CONTENT:
                $text = 'No Content';
                break;

            case self::CODE_205_RESET_CONTENT:
                $text = 'Reset Content';
                break;

            case self::CODE_206_PARTIAlL_CONTENT:
                $text = 'Partial Content';
                break;

            // 300

            case self::CODE_300_MULTIPLE_CHOICES:
                $text = 'Multiple Choices';
                break;

            case self::CODE_301_MOVED_PERMANENTLY:
                $text = 'Moved Permanently';
                break;

            case self::CODE_302_MOVED_TEMPORARILY:
                $text = 'Moved Temporarily';
                break;

            case self::CODE_303_SEE_OTHER:
                $text = 'See Other';
                break;

            case self::CODE_304_NOT_MODIFIED:
                $text = 'Not Modified';
                break;

            case self::CODE_305_USE_PROXY:
                $text = 'Use Proxy';
                break;

            // 400

            case self::CODE_400_BAD_REQUEST:
                $text = 'Bad Request';
                break;

            case self::CODE_401_UNAUTHORIZED:
                $text = 'Unauthorized';
                break;

            case self::CODE_402_PAYMENT_REQUIRED:
                $text = 'Payment Required';
                break;

            case self::CODE_403_FORBIDDEN:
                $text = 'Forbidden';
                break;

            case self::CODE_404_NOT_FOUND:
                $text = 'Not Found';
                break;

            case self::CODE_405_METHOD_NOT_ALLOWED:
                $text = 'Method Not Allowed';
                break;

            case self::CODE_406_NOT_ACCEPTABLE:
                $text = 'Not Acceptable';
                break;

            case self::CODE_407_PROXY_AUTHENTICATION_REQUIRED:
                $text = 'Proxy Authentication Required';
                break;

            case self::CODE_408_REQUEST_TIME_OUT:
                $text = 'Request Time-out';
                break;

            case self::CODE_409_CONFLICT:
                $text = 'Conflict';
                break;

            case self::CODE_410_GONE:
                $text = 'Gone';
                break;

            case self::CODE_411_LENGTH_REQUIRED:
                $text = 'Length Required';
                break;

            case self::CODE_412_PRECONDITION_FAILED:
                $text = 'Precondition Failed';
                break;

            case self::CODE_413_REQUEST_ENTITY_TOO_LARGE:
                $text = 'Request Entity Too Large';
                break;

            case self::CODE_414_REQUEST_URI_TOO_LARGE:
                $text = 'Request-URI Too Large';
                break;

            case self::CODE_415_UNSUPPORTED_MEDIA_TYPE:
                $text = 'Unsupported Media Type';
                break;

            // 500

            case self::CODE_500_INTERNAL_SERVER_ERROR:
                $text = 'Internal Server Error';
                break;

            case self::CODE_501_NOT_IMPLEMENTED:
                $text = 'Not Implemented';
                break;

            case self::CODE_502_BAD_GATEWAY:
                $text = 'Bad Gateway';
                break;

            case self::CODE_503_SERVICE_UNAVAILABLE:
                $text = 'Service Unavailable';
                break;

            case self::CODE_504_GATEWAY_TIME_OUT:
                $text = 'Gateway Time-out';
                break;

            case self::CODE_505_HTTP_VERSION_NOT_SUPPORTED:
                $text = 'HTTP Version not supported';
                break;

            default:
                throw new HttpException("Unknown http status code {$code}");
        }

        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';

        header("{$protocol} {$code} {$text}");
        http_response_code($code);
    }

    /**
     * Возвращает код заданного HTTP заголовка
     *
     * @note не в окружении веб-сервера (например, в CLI), будет возвращено false
     * @return int|false
     */
    static public function getHttpCode()
    {
        return http_response_code();
    }
}