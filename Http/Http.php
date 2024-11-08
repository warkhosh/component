<?php

namespace Warkhosh\Component\Http;

use Warkhosh\Component\Http\Exception\HttpException;

class Http
{
    /**
     * Список кодов состояния HTTP
     * https://ru.wikipedia.org/wiki/Список_кодов_состояния_HTTP
     */
    public const CODE_100_CONTINUE = 100;
    public const CODE_101_SWITCHING_PROTOCOLS = 101;
    public const CODE_200_OK = 200;
    public const CODE_201_CREATED = 201;
    public const CODE_202_ACCEPTED = 202;
    public const CODE_203_NON_AUTHORITATIVE_INFORMATION = 203;
    public const CODE_204_NO_CONTENT = 204;
    public const CODE_205_RESET_CONTENT = 205;
    public const CODE_206_PARTIAlL_CONTENT = 206;

    public const CODE_300_MULTIPLE_CHOICES = 300;
    public const CODE_301_MOVED_PERMANENTLY = 301;
    public const CODE_302_MOVED_TEMPORARILY = 302;
    public const CODE_303_SEE_OTHER = 303;
    public const CODE_304_NOT_MODIFIED = 304;
    public const CODE_305_USE_PROXY = 305;

    public const CODE_400_BAD_REQUEST = 400;
    public const CODE_401_UNAUTHORIZED = 401;
    public const CODE_402_PAYMENT_REQUIRED = 402;
    public const CODE_403_FORBIDDEN = 403;
    public const CODE_404_NOT_FOUND = 404;
    public const CODE_405_METHOD_NOT_ALLOWED = 405;
    public const CODE_406_NOT_ACCEPTABLE = 406;
    public const CODE_407_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const CODE_408_REQUEST_TIME_OUT = 408;
    public const CODE_409_CONFLICT = 409;
    public const CODE_410_GONE = 410;
    public const CODE_411_LENGTH_REQUIRED = 411;
    public const CODE_412_PRECONDITION_FAILED = 412;
    public const CODE_413_REQUEST_ENTITY_TOO_LARGE = 413;
    public const CODE_414_REQUEST_URI_TOO_LARGE = 414;
    public const CODE_415_UNSUPPORTED_MEDIA_TYPE = 415;

    public const CODE_500_INTERNAL_SERVER_ERROR = 500;
    public const CODE_501_NOT_IMPLEMENTED = 501;
    public const CODE_502_BAD_GATEWAY = 502;
    public const CODE_503_SERVICE_UNAVAILABLE = 503;
    public const CODE_504_GATEWAY_TIME_OUT = 504;
    public const CODE_505_HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Устанавливает по полученному коду заголовок
     *
     * @param int $code
     * @return void
     * @throws HttpException
     */
    public static function setHttpHeader(int $code): void
    {
        $text = match ($code) {
            self::CODE_100_CONTINUE => 'Continue',
            self::CODE_101_SWITCHING_PROTOCOLS => 'Switching Protocols',
            self::CODE_200_OK => 'OK',
            self::CODE_201_CREATED => 'Created',
            self::CODE_202_ACCEPTED => 'Accepted',
            self::CODE_203_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
            self::CODE_204_NO_CONTENT => 'No Content',
            self::CODE_205_RESET_CONTENT => 'Reset Content',
            self::CODE_206_PARTIAlL_CONTENT => 'Partial Content',
            self::CODE_300_MULTIPLE_CHOICES => 'Multiple Choices',
            self::CODE_301_MOVED_PERMANENTLY => 'Moved Permanently',
            self::CODE_302_MOVED_TEMPORARILY => 'Moved Temporarily',
            self::CODE_303_SEE_OTHER => 'See Other',
            self::CODE_304_NOT_MODIFIED => 'Not Modified',
            self::CODE_305_USE_PROXY => 'Use Proxy',
            self::CODE_400_BAD_REQUEST => 'Bad Request',
            self::CODE_401_UNAUTHORIZED => 'Unauthorized',
            self::CODE_402_PAYMENT_REQUIRED => 'Payment Required',
            self::CODE_403_FORBIDDEN => 'Forbidden',
            self::CODE_404_NOT_FOUND => 'Not Found',
            self::CODE_405_METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::CODE_406_NOT_ACCEPTABLE => 'Not Acceptable',
            self::CODE_407_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
            self::CODE_408_REQUEST_TIME_OUT => 'Request Time-out',
            self::CODE_409_CONFLICT => 'Conflict',
            self::CODE_410_GONE => 'Gone',
            self::CODE_411_LENGTH_REQUIRED => 'Length Required',
            self::CODE_412_PRECONDITION_FAILED => 'Precondition Failed',
            self::CODE_413_REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
            self::CODE_414_REQUEST_URI_TOO_LARGE => 'Request-URI Too Large',
            self::CODE_415_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
            self::CODE_500_INTERNAL_SERVER_ERROR => 'Internal Server Error',
            self::CODE_501_NOT_IMPLEMENTED => 'Not Implemented',
            self::CODE_502_BAD_GATEWAY => 'Bad Gateway',
            self::CODE_503_SERVICE_UNAVAILABLE => 'Service Unavailable',
            self::CODE_504_GATEWAY_TIME_OUT => 'Gateway Time-out',
            self::CODE_505_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version not supported',
            default => throw new HttpException("Unknown http status code {$code}"),
        };

        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';

        header("{$protocol} {$code} {$text}");
        http_response_code($code);
    }

    /**
     * Возвращает код заданного HTTP заголовка
     *
     * @note не в окружении веб-сервера (например, в CLI), будет возвращено false
     * @return bool|int
     */
    public static function getHttpCode(): bool|int
    {
        return http_response_code();
    }
}
