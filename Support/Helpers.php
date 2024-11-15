<?php

use Warkhosh\Component\Collection\Collection;
use Warkhosh\Component\Server\AppServer;
use Warkhosh\Variable\VarArray;
use Warkhosh\Variable\VarFloat;
use Warkhosh\Variable\VarInt;
use Warkhosh\Variable\VarStr;

if (false && ! function_exists('getConfig')) {
    /**
     * Короткий синтаксис обращения к конфигу
     *
     * @note example
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    function getConfig(string $name = null, mixed $default = null): mixed
    {
        try {
            $appConfig = \Warkhosh\Component\Config\AppConfig::getInstance();

            //$appConfig->setBasePath(app()->getBasePath() . '/Application/Configs');

            return $appConfig->get($name, $default);

        } catch (Throwable $e) {
            //Log::error($e);
        }

        return $default;
    }
}

if (! function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param string|null $value
     * @return string
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8', false);
        // return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (! function_exists('server')) {
    /**
     * @return AppServer
     */
    function server(): AppServer
    {
        return AppServer::getInstance();
    }
}

if (! function_exists('getNum')) {
    /**
     * Проверка значения на положительное целое цело (в случае неудачи вернет установленное значение)
     *
     * @param mixed $num проверяемое значение
     * @param int $default значение при неудачной проверке
     * @return int
     * @throws Exception
     */
    function getNum(mixed $num = 0, int $default = 0): int
    {
        return VarInt::getMakePositiveInteger($num, $default);
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
     * @param bool $strict - флаг соответствия типа
     * @return array
     */
    function getArrayWrap($items, bool $strict = true): array
    {
        return $strict ? (is_array($items) ? $items : []) : (array)$items;
    }
}

if (! function_exists('isTrue')) {
    /**
     * Проверка истинности значения;
     *
     * @param mixed $var
     * @param bool $strict
     * @return bool
     * @throws Exception
     */
    function isTrue(mixed $var = null, bool $strict = false): bool
    {
        if ($var === true) {
            return true;
        }

        if (is_array($var) || is_object($var)) {
            return false;
        }

        if ($strict === false) {
            if ((int)$var === 1 || VarStr::getLower(trim($var)) === 'true') {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('isFalse')) {
    /**
     * Проверка истинности значения;
     *
     * @param mixed $var
     * @param bool $strict
     * @return bool
     * @throws Exception
     */
    function isFalse(mixed $var = null, bool $strict = false): bool
    {
        if ($var === false) {
            return true;
        }

        if (is_array($var) || is_object($var)) {
            return false;
        }

        if ($strict === false) {
            if (((int)$var === 0 || VarStr::getLower(trim($var)) === 'false')) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('itZero')) {
    /**
     * Проверка указанного значения на равенство нулю;
     *
     * @param mixed $var
     * @return bool
     */
    function itZero(mixed $var): bool
    {
        if (is_numeric($var) && intval($var) === 0) {
            return true;
        }

        return false;
    }
}

if (! function_exists('getEncoding')) {
    /**
     * @param mixed $str
     * @return string|null
     */
    function getEncoding(mixed $str): string|null
    {
        $cp_list = ['utf-8', 'windows-1251'];
        $encoding = mb_detect_encoding($str, mb_detect_order(), false);
        $clean_str = $str = VarStr::getMake($str);

        if ($encoding === 'UTF-8') {
            $clean_str = mb_convert_encoding($str, 'UTF-8');
        }

        foreach ($cp_list as $codePage) {
            if (md5($str) === @md5(@iconv($codePage, $codePage.'//IGNORE', $clean_str))) {
                return $codePage;
            }
        }

        return null;
    }
}

if (! function_exists('convertToUTF')) {
    /**
     * Безопасное преобразование строки в utf-8
     *
     * @param string|null $text
     * @return string
     * @throws Exception
     */
    function convertToUTF(?string $text = ''): string
    {
        return VarStr::getTransformToEncoding($text, 'UTF-8');
        //$encoding = mb_detect_encoding($text, mb_detect_order(), false);
        //
        //if ($encoding === "UTF-8") {
        //    $text = mb_convert_encoding($text, 'UTF-8');
        //}
        //
        //$out = @iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8//IGNORE", $text);
        //
        //return $out;
    }
}

if (! function_exists('convert_entity')) {
    /**
     * Метод для работы \Warkhosh\Variable\VarStr::decodeEntities()
     * Содержит полный рекомендованный список HTML сущностей, для преобразования их в специальные символы
     *
     * @param array $matches
     * @param bool $destroy
     * @return string
     */
    function convert_entity(array $matches = [], bool $destroy = true): string
    {
        static $table = [
            'quot' => '&#34;',
            'amp' => '&#38;',
            'lt' => '&#60;',
            'gt' => '&#62;',
            'OElig' => '&#338;',
            'oelig' => '&#339;',
            'Scaron' => '&#352;',
            'scaron' => '&#353;',
            'Yuml' => '&#376;',
            'circ' => '&#710;',
            'tilde' => '&#732;',
            'ensp' => '&#8194;',
            'emsp' => '&#8195;',
            'thinsp' => '&#8201;',
            'zwnj' => '&#8204;',
            'zwj' => '&#8205;',
            'lrm' => '&#8206;',
            'rlm' => '&#8207;',
            'ndash' => '&#8211;',
            'mdash' => '&#8212;',
            'lsquo' => '&#8216;',
            'rsquo' => '&#8217;',
            'sbquo' => '&#8218;',
            'ldquo' => '&#8220;',
            'rdquo' => '&#8221;',
            'bdquo' => '&#8222;',
            'dagger' => '&#8224;',
            'Dagger' => '&#8225;',
            'permil' => '&#8240;',
            'lsaquo' => '&#8249;',
            'rsaquo' => '&#8250;',
            'euro' => '&#8364;',
            'fnof' => '&#402;',
            'Alpha' => '&#913;',
            'Beta' => '&#914;',
            'Gamma' => '&#915;',
            'Delta' => '&#916;',
            'Epsilon' => '&#917;',
            'Zeta' => '&#918;',
            'Eta' => '&#919;',
            'Theta' => '&#920;',
            'Iota' => '&#921;',
            'Kappa' => '&#922;',
            'Lambda' => '&#923;',
            'Mu' => '&#924;',
            'Nu' => '&#925;',
            'Xi' => '&#926;',
            'Omicron' => '&#927;',
            'Pi' => '&#928;',
            'Rho' => '&#929;',
            'Sigma' => '&#931;',
            'Tau' => '&#932;',
            'Upsilon' => '&#933;',
            'Phi' => '&#934;',
            'Chi' => '&#935;',
            'Psi' => '&#936;',
            'Omega' => '&#937;',
            'alpha' => '&#945;',
            'beta' => '&#946;',
            'gamma' => '&#947;',
            'delta' => '&#948;',
            'epsilon' => '&#949;',
            'zeta' => '&#950;',
            'eta' => '&#951;',
            'theta' => '&#952;',
            'iota' => '&#953;',
            'kappa' => '&#954;',
            'lambda' => '&#955;',
            'mu' => '&#956;',
            'nu' => '&#957;',
            'xi' => '&#958;',
            'omicron' => '&#959;',
            'pi' => '&#960;',
            'rho' => '&#961;',
            'sigmaf' => '&#962;',
            'sigma' => '&#963;',
            'tau' => '&#964;',
            'upsilon' => '&#965;',
            'phi' => '&#966;',
            'chi' => '&#967;',
            'psi' => '&#968;',
            'omega' => '&#969;',
            'thetasym' => '&#977;',
            'upsih' => '&#978;',
            'piv' => '&#982;',
            'bull' => '&#8226;',
            'hellip' => '&#8230;',
            'prime' => '&#8242;',
            'Prime' => '&#8243;',
            'oline' => '&#8254;',
            'frasl' => '&#8260;',
            'weierp' => '&#8472;',
            'image' => '&#8465;',
            'real' => '&#8476;',
            'trade' => '&#8482;',
            'alefsym' => '&#8501;',
            'larr' => '&#8592;',
            'uarr' => '&#8593;',
            'rarr' => '&#8594;',
            'darr' => '&#8595;',
            'harr' => '&#8596;',
            'crarr' => '&#8629;',
            'lArr' => '&#8656;',
            'uArr' => '&#8657;',
            'rArr' => '&#8658;',
            'dArr' => '&#8659;',
            'hArr' => '&#8660;',
            'forall' => '&#8704;',
            'part' => '&#8706;',
            'exist' => '&#8707;',
            'empty' => '&#8709;',
            'nabla' => '&#8711;',
            'isin' => '&#8712;',
            'notin' => '&#8713;',
            'ni' => '&#8715;',
            'prod' => '&#8719;',
            'sum' => '&#8721;',
            'minus' => '&#8722;',
            'lowast' => '&#8727;',
            'radic' => '&#8730;',
            'prop' => '&#8733;',
            'infin' => '&#8734;',
            'ang' => '&#8736;',
            'and' => '&#8743;',
            'or' => '&#8744;',
            'cap' => '&#8745;',
            'cup' => '&#8746;',
            'int' => '&#8747;',
            'there4' => '&#8756;',
            'sim' => '&#8764;',
            'cong' => '&#8773;',
            'asymp' => '&#8776;',
            'ne' => '&#8800;',
            'equiv' => '&#8801;',
            'le' => '&#8804;',
            'ge' => '&#8805;',
            'sub' => '&#8834;',
            'sup' => '&#8835;',
            'nsub' => '&#8836;',
            'sube' => '&#8838;',
            'supe' => '&#8839;',
            'oplus' => '&#8853;',
            'otimes' => '&#8855;',
            'perp' => '&#8869;',
            'sdot' => '&#8901;',
            'lceil' => '&#8968;',
            'rceil' => '&#8969;',
            'lfloor' => '&#8970;',
            'rfloor' => '&#8971;',
            'lang' => '&#9001;',
            'rang' => '&#9002;',
            'loz' => '&#9674;',
            'spades' => '&#9824;',
            'clubs' => '&#9827;',
            'hearts' => '&#9829;',
            'diams' => '&#9830;',
            'nbsp' => '&#160;',
            'iexcl' => '&#161;',
            'cent' => '&#162;',
            'pound' => '&#163;',
            'curren' => '&#164;',
            'yen' => '&#165;',
            'brvbar' => '&#166;',
            'sect' => '&#167;',
            'uml' => '&#168;',
            'copy' => '&#169;',
            'ordf' => '&#170;',
            'laquo' => '&#171;',
            'not' => '&#172;',
            'shy' => '&#173;',
            'reg' => '&#174;',
            'macr' => '&#175;',
            'deg' => '&#176;',
            'plusmn' => '&#177;',
            'sup2' => '&#178;',
            'sup3' => '&#179;',
            'acute' => '&#180;',
            'micro' => '&#181;',
            'para' => '&#182;',
            'middot' => '&#183;',
            'cedil' => '&#184;',
            'sup1' => '&#185;',
            'ordm' => '&#186;',
            'raquo' => '&#187;',
            'frac14' => '&#188;',
            'frac12' => '&#189;',
            'frac34' => '&#190;',
            'iquest' => '&#191;',
            'Agrave' => '&#192;',
            'Aacute' => '&#193;',
            'Acirc' => '&#194;',
            'Atilde' => '&#195;',
            'Auml' => '&#196;',
            'Aring' => '&#197;',
            'AElig' => '&#198;',
            'Ccedil' => '&#199;',
            'Egrave' => '&#200;',
            'Eacute' => '&#201;',
            'Ecirc' => '&#202;',
            'Euml' => '&#203;',
            'Igrave' => '&#204;',
            'Iacute' => '&#205;',
            'Icirc' => '&#206;',
            'Iuml' => '&#207;',
            'ETH' => '&#208;',
            'Ntilde' => '&#209;',
            'Ograve' => '&#210;',
            'Oacute' => '&#211;',
            'Ocirc' => '&#212;',
            'Otilde' => '&#213;',
            'Ouml' => '&#214;',
            'times' => '&#215;',
            'Oslash' => '&#216;',
            'Ugrave' => '&#217;',
            'Uacute' => '&#218;',
            'Ucirc' => '&#219;',
            'Uuml' => '&#220;',
            'Yacute' => '&#221;',
            'THORN' => '&#222;',
            'szlig' => '&#223;',
            'agrave' => '&#224;',
            'aacute' => '&#225;',
            'acirc' => '&#226;',
            'atilde' => '&#227;',
            'auml' => '&#228;',
            'aring' => '&#229;',
            'aelig' => '&#230;',
            'ccedil' => '&#231;',
            'egrave' => '&#232;',
            'eacute' => '&#233;',
            'ecirc' => '&#234;',
            'euml' => '&#235;',
            'igrave' => '&#236;',
            'iacute' => '&#237;',
            'icirc' => '&#238;',
            'iuml' => '&#239;',
            'eth' => '&#240;',
            'ntilde' => '&#241;',
            'ograve' => '&#242;',
            'oacute' => '&#243;',
            'ocirc' => '&#244;',
            'otilde' => '&#245;',
            'ouml' => '&#246;',
            'divide' => '&#247;',
            'oslash' => '&#248;',
            'ugrave' => '&#249;',
            'uacute' => '&#250;',
            'ucirc' => '&#251;',
            'uuml' => '&#252;',
            'yacute' => '&#253;',
            'thorn' => '&#254;',
            'yuml' => '&#255;',
        ];

        if (isset($table[$matches[1]])) {
            return $table[$matches[1]];
        }

        $result = $destroy ? '' : $matches[0];

        if (gettype($result) !== 'string') {
            $result = (string)$result;
        }

        return $result;
    }
}

if (! function_exists('isEmpty')) {
    /**
     * Проверка строки на пустое значение.
     *
     * @param mixed $value
     * @return bool
     */
    function isEmpty(mixed $value = ''): bool
    {
        if (is_null($value) || is_bool($value) || is_array($value) || is_object($value)) {
            return true;
        }

        $str = VarStr::getMake($value);
        $str = str_replace(["\n", "\t", "\r", '&nbsp;'], ['', '', '', ' '], $str);
        $str = trim($str, "\x00..\x1F");
        $str = trim($str, chr(194).chr(160));

        return preg_match("/(\S+)/i", $str) === 0;
    }
}

if (! function_exists('isNotEmpty')) {
    /**
     * Проверка строки на НЕ пустое значение.
     *
     * @param mixed $value
     * @return bool
     */
    function isNotEmpty(mixed $value = ''): bool
    {
        return ! isEmpty($value);
    }
}

if (! function_exists('emptyStringTo')) {
    /**
     * Возвращает $default значение если указанная строка и не пустая.
     *
     * @param mixed $value
     * @param mixed|null $default
     * @return mixed
     */
    function emptyStringTo(mixed $value = '', mixed $default = null): mixed
    {
        if (is_null($value) || is_bool($value) || is_array($value) || is_object($value)) {
            return $default;
        }

        $str = VarStr::getMake($value);
        $str = str_replace(["\n", "\t", "\r", '&nbsp;'], ['', '', '', ' '], $str);
        $str = trim($str, "\x00..\x1F");
        $str = trim($str, chr(194).chr(160));

        return preg_match("/(\S+)/i", $str) === 0 ? $default : $value;
    }
}

if (! function_exists('itBlank')) {
    /**
     * Определяет, заполнено ли значение.
     *
     * @note: Отличается от isEmpty() более широкими вариантами применения проверок разных типов.
     *
     * @param mixed $value
     * @return bool
     */
    function itBlank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        // Возвращаем что значение не пустое если оно число, логического значение или массив со значениями
        if (is_numeric($value) || is_bool($value) || (is_array($value) && count($value))) {
            return false;
        }

        $value = VarStr::getMake($value);
        $value = str_replace(["\n", "\t", "\r", '&nbsp;'], ['', '', '', ' '], $value);
        $value = trim($value, "\x00..\x1F");

        return preg_match("/(\S+)/i", $value) === 0;
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

if (! function_exists('toSnakeCase')) {
    /**
     * Преобразование Camel case в Snake case!
     *
     * @param string $input
     * @return string
     */
    function toSnakeCase(string $input = ''): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }
}

if (! function_exists('toCamelCase')) {
    /**
     * Преобразование Snake case в Camel case!
     *
     * @param string $input
     * @return string
     * @throws Exception
     */
    function toCamelCase(string $input = ''): string
    {
        $input = str_replace('-', '_', $input);

        return implode('', VarArray::ucfirst(explode('_', $input)));
    }
}

if (! function_exists('round_up')) {
    /**
     * Округление в большую сторону
     *
     * @note в идеале использовать BC Math Функции (http://php.net/manual/ru/ref.bc.php)
     *
     * @param mixed $number
     * @param int $precision
     * @return float
     */
    function round_up(mixed $number, int $precision = 2): float
    {
        return VarFloat::getMake($number, $precision, 'upward');
    }
}

if (! function_exists('round_down')) {
    /**
     * Округление в меньшую сторону
     *
     * @note в идеале использовать BC Math Функции (http://php.net/manual/ru/ref.bc.php)
     *
     * @param mixed $number
     * @param int $precision
     * @return float
     */
    function round_down(mixed $number, int $precision = 2): float
    {
        return VarFloat::getMake($number, $precision, 'downward');
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
