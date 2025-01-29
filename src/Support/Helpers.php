<?php

use Warkhosh\Component\Collection\Collection;
use Warkhosh\Component\Server\AppServer;

if (! function_exists('server')) {
    /**
     * @return AppServer
     */
    function server(): AppServer
    {
        return AppServer::getInstance();
    }
}

if (! function_exists('collection')) {
    /**
     * Создайте коллекцию из заданного значения
     *
     * @param array|float|int|string $input
     * @return Collection
     */
    function collection(array|float|int|string $input = []): Collection
    {
        return new Collection($input);
    }
}

if (! function_exists('getArrayWrap')) {
    /**
     * Быстрое преобразование значения в массив
     *
     * @param $items
     * @param bool $strict флаг соответствия типа
     * @return array
     * @deprecated заменить на getValueData() который есть в warkhosh/assist
     */
    function getArrayWrap($items, bool $strict = true): array
    {
        return $strict ? (is_array($items) ? $items : []) : (array)$items;
    }
}

if (! function_exists('getNumeralEnding')) {
    /**
     * Склонение числительных
     *
     * @param int $number
     * @param string $str
     * @param string $ending1
     * @param string $ending24
     * @param string $ending50
     * @return string
     */
    function getNumeralEnding(
        int $number = 0,
        string $str = '',
        string $ending1 = '',
        string $ending24 = '',
        string $ending50 = ''
    ): string {
        $locale = 'ru';

        if ($locale == 'ru') {
            $number = $number % 100;

            if ($number >= 11 && $number <= 19) {
                return $str.$ending50;

            } else {
                $number = $number % 10;

                switch ($number) {
                    case 1:
                        return $str.$ending1;
                        break;
                    case 2:
                    case 3:
                    case 4:
                        return $str.$ending24;
                        break;

                    default:
                        return $str.$ending50;
                }
            }
        } elseif ($locale == 'en') {
            // @todo: сделать для английского языка пока не доводилось
            return $str;
        } else {
            return '';
        }
    }
}

if (! function_exists('addFieldFirst')) {
    /**
     * Вставка в массив запись, что это первый элемент
     *
     * @param array $row
     * @return array
     */
    function addFieldFirst(array $row): array
    {
        return array_merge($row, ['first_child' => 1]);
    }
}

if (! function_exists('httpResponseCode')) {
    /**
     * Устанавливает по полученному коду ответ заголовка
     *
     * @param int|null $code
     * @return bool|int
     * @throws Exception
     * @deprecated все реализации надо переписать на setHttpResponseCode() и getHttpResponseCode() которые есть в warkhosh/assist
     */
    function httpResponseCode(?int $code = null): bool|int
    {
        if ($code !== null) {
            $text = match ($code) {
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Moved Temporarily',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Time-out',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Large',
                415 => 'Unsupported Media Type',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Time-out',
                505 => 'HTTP Version not supported',
                default => throw new Exception('Unknown http status code "'.htmlentities($code).'"'),
            };

            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';

            header($protocol.' '.$code.' '.$text);
            http_response_code($code);

            return true;
        }

        // проверка результата http_response_code(), не в окружении веб-сервера (например в CLI) он возвращает bool
        return ($code = http_response_code()) > 0 ? $code : 200;
    }
}

if (! function_exists('getMicroTime')) {
    /**
     * @return float
     */
    function getMicroTime(): float
    {
        [$usec, $sec] = explode(' ', microtime());

        return (float)$usec + (float)$sec;
    }
}

if (! function_exists('getPriceFormat')) {
    /**
     * Форматирует число с разделением групп
     *
     * @param float|int|string $number число
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return string
     */
    function getPriceFormat(
        float|int|string $number,
        int $decimals = 0,
        string $dec_point = ' ',
        string $thousands_sep = ' '
    ): string {
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
}

if (! function_exists('getPhoneFormat')) {
    /**
     * Форматирует строку телефона в читаемый вариант
     *
     * @param string $phone
     * @param int $code_length
     * @param int $first_part_length
     * @param int $second_part_length
     * @return string
     */
    function getPhoneFormat(
        string $phone = '',
        int $code_length = 3,
        int $first_part_length = 3,
        int $second_part_length = 2
    ): string {
        $str = '';
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (mb_strlen($phone) == 10) {
            $phone = "7{$phone}";
        }

        if (mb_strlen($phone) == 11) {
            $str = '+7';
            $str .= ' ('.mb_substr($phone, 1, $code_length).')';
            $str .= ' '.mb_substr($phone, (1 + $code_length), $first_part_length);
            $str .= '-'.mb_substr($phone, (1 + $code_length + $first_part_length), $second_part_length);
            $str .= '-'.mb_substr($phone, (1 + $code_length + $first_part_length + $second_part_length), 10);
        }

        return $str;
    }
}

if (! function_exists('base64url_encode')) {
    /**
     * @param string|null $data
     * @return string
     */
    function base64url_encode(?string $data = ''): string
    {
        return rtrim(strtr(base64_encode((string)$data), '+/', '-_'), '=');
    }
}

if (! function_exists('base64url_decode')) {
    /**
     * @param string|null $data
     * @return string
     */
    function base64url_decode(?string $data = ''): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen((string)$data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (! function_exists('str_replace_once')) {
    /**
     * Замена первой найденной строки.
     *
     * @param array|string $search
     * @param array|string $replace
     * @param string $text
     * @return string
     */
    function str_replace_once(array|string $search, array|string $replace, string $text): string
    {
        if (is_string($search)) {
            $pos = mb_strpos($text, $search);

            if ($pos !== false) {
                // return substr_replace($text, (string)$replace, $pos, strlen($search)); шалит!
                return mb_substr($text, 0, $pos).$replace.mb_substr($text, $pos + mb_strlen($search));
            }

            return $text;
        }

        if (is_array($search) && is_bool($replace_is_string = is_string($replace))) {
            foreach ($search as $key => $searchText) {
                $replaceText = $replace_is_string ? $replace : ($replace[$key] ?? '');
                $text = str_replace_once($searchText, $replaceText, $text);
            }

            return $text;
        }

        trigger_error('invalid parameters passed');

        return $text;
    }
}

if (! function_exists('str_replace_last')) {
    /**
     * Замена последней найденной строки.
     *
     * @param array|string $search
     * @param array|string $replace
     * @param string $text
     * @return string
     */
    function str_replace_last(array|string $search, array|string $replace, string $text): string
    {
        if (is_string($search)) {
            $pos = strrpos($text, $search);

            if ($pos !== false) {
                // return substr_replace($text, (string)$replace, $pos, strlen($search)); шалит!
                return mb_substr($text, 0, $pos).$replace.mb_substr($text, $pos + mb_strlen($search));
            }

            return $text;
        }

        if (is_array($search) && is_bool($replace_is_string = is_string($replace))) {
            foreach ($search as $key => $searchText) {
                $replaceText = $replace_is_string ? $replace : ($replace[$key] ?? '');
                $text = str_replace_last($searchText, $replaceText, $text);
            }

            return $text;
        }

        trigger_error('invalid parameters passed');

        return $text;
    }
}

if (! function_exists('getObjectToArray')) {
    /**
     * Преобразование объектов stdClass в многомерные массивы
     *
     * @param array|stdClass $data
     * @return array
     */
    function getObjectToArray(array|stdClass $data): array
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map(__FUNCTION__, $data);

        } else {
            return (array)$data;
        }
    }
}

if (! function_exists('getArrayToObject')) {
    /**
     * Функция преобразования многомерных массивов в объекты stdClass.
     *
     * @param array|stdClass $data
     * @return array|stdClass
     */
    function getArrayToObject(array|stdClass $data): array|stdClass
    {
        if (is_array($data)) {
            return (object)array_map(__FUNCTION__, $data);
        }

        return $data;
    }
}

if (! function_exists('getAmountMemory')) {
    /**
     * Возвращает размер памяти в указанной единице измерения.
     *
     * @param int $size
     * @param string $unit
     * @param bool $designation
     * @return float|int|string
     */
    function getAmountMemory(int $size, string $unit = 'b', bool $designation = true): float|int|string
    {
        if ($unit == 'kb') {
            return round($size / 1024, 2).($designation ? ' Kb' : '');

        } elseif ($unit == 'mb') {
            return round($size / 1048576, 2).($designation ? ' Mb' : '');
        }

        return $size.($designation ? ' Byte' : '');
    }
}

if (! function_exists('getFileSize')) {
    /**
     * Возвращает размер файла в указанной единице измерения.
     *
     * @param int $size
     * @param string $unit
     * @param bool $designation
     * @return string
     */
    function getFileSize(int $size, string $unit = 'b', bool $designation = true): string
    {
        return getAmountMemory($size, $unit, $designation);
    }
}

if (! function_exists('getMemoryPeakUsage')) {
    /**
     * Возвращает пиковое значение объема памяти, выделенное PHP сценарию.
     *
     * @note при указании единицы измерения будет возвращен результат с преобразованием
     *
     * @param string|null $unit
     * @param bool $designation
     * @return int|string
     */
    function getMemoryPeakUsage(string $unit = null, bool $designation = false): int|string
    {
        $memory_usage = function_exists('memory_get_peak_usage') ? memory_get_peak_usage(true) : 0;

        if ($unit === 'mb' || $unit === 'kb' || $unit === 'byte') {
            return getAmountMemory($memory_usage, $unit, $designation);
        }

        return $memory_usage;
    }
}

if (! function_exists('array_to_xml')) {
    /**
     * @param array $arr
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     *
     * @example
     * $dom = new DOMDocument;
     * $dom->preserveWhiteSpace = FALSE;
     * $dom->loadXML($xml = array_to_xml([...], new SimpleXMLElement('<root/>'))->asXML());
     * $dom->formatOutput = TRUE;
     * echo $dom->saveXml();
     */
    function array_to_xml(array $arr, SimpleXMLElement $xml): SimpleXMLElement
    {
        foreach ($arr as $k => $v) {

            $attrArr = [];
            $kArray = explode(' ', $k);
            $tag = array_shift($kArray);

            if (count($kArray) > 0) {
                foreach ($kArray as $attrValue) {
                    $attrArr[] = explode('=', $attrValue);
                }
            }

            if (is_array($v)) {
                if (is_numeric($k)) {
                    array_to_xml($v, $xml);
                } else {
                    $child = $xml->addChild($tag);
                    if (isset($attrArr)) {
                        foreach ($attrArr as $attrArrV) {
                            $child->addAttribute($attrArrV[0], $attrArrV[1]);
                        }
                    }
                    array_to_xml($v, $child);
                }
            } else {
                $child = $xml->addChild($tag, $v);
                if (isset($attrArr)) {
                    foreach ($attrArr as $attrArrV) {
                        $child->addAttribute($attrArrV[0], $attrArrV[1]);
                    }
                }
            }
        }

        return $xml;
    }
}
