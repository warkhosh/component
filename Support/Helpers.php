<?php

if (! function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param string $value
     * @return string
     */
    function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        // return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (! function_exists('server')) {
    /**
     * @return Warkhosh\Component\Server\AppServer
     */
    function server()
    {
        return Warkhosh\Component\Server\AppServer::getInstance();
    }
}

if (! function_exists('getNum')) {
    /**
     * Проверка значения на положительное целое цело (в случае неудачи вернет установленное значение)
     *
     * @param int $num     - проверяемое значение
     * @param int $default - значение при неудачной проверке
     * @return int
     */
    function getNum($num = 0, $default = 0)
    {
        return \Warkhosh\Variable\VarInt::getMakePositiveInteger($num, $default);
    }
}

if (! function_exists('collection')) {
    /**
     * Создайте коллекцию из заданного значения
     *
     * @param array|string|integer|float $input
     * @return \ Warkhosh\Component\Collection\Collection
     */
    function collection($input = [])
    {
        return (new \ Warkhosh\Component\Collection\Collection($input));
    }
}

if (! function_exists('getArrayWrap')) {
    /**
     * Быстрое преобразование значения в массив
     *
     * @param      $items
     * @param bool $strict - флаг соответствия типа
     * @return array
     */
    function getArrayWrap($items, $strict = true)
    {
        return $strict ? (is_array($items) ? $items : []) : (array)$items;
    }
}

if (! function_exists('isTrue')) {
    /**
     * Проверка истинности значения;
     *
     * @param null $var
     * @param bool $strict
     * @return bool
     */
    function isTrue($var = null, $strict = false)
    {
        if ($var === true) {
            return true;
        }

        if (is_array($var) || is_object($var)) {
            return false;
        }

        if ($strict === false) {
            if ((int)$var === 1 || \Warkhosh\Variable\VarStr::getLower(trim($var)) === 'true') {
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
     * @param null $var
     * @param bool $strict
     * @return bool
     */
    function isFalse($var = null, $strict = false)
    {
        if ($var === false) {
            return true;
        }

        if (is_array($var) || is_object($var)) {
            return false;
        }

        if ($strict === false) {
            if (((int)$var === 0 || \Warkhosh\Variable\VarStr::getLower(trim($var)) === 'false')) {
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
     * @param integer $var
     * @return bool
     */
    function itZero($var = 0)
    {
        if (is_numeric($var) && intval($var) === 0) {
            return true;
        }

        return false;
    }
}

if (! function_exists('getEncoding')) {
    /**
     * @param string $str
     * @return string|null
     */
    function getEncoding($str)
    {
        $cp_list = ['utf-8', 'windows-1251'];
        $encoding = mb_detect_encoding($str, mb_detect_order(), false);
        $clean_str = $str = \Warkhosh\Variable\VarStr::getMakeString($str);

        if ($encoding === "UTF-8") {
            $clean_str = mb_convert_encoding($str, 'UTF-8');
        }

        foreach ($cp_list as $k => $codePage) {
            if (md5($str) === @md5(@iconv($codePage, $codePage . '//IGNORE', $clean_str))) {
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
     * @param string $text
     * @return string
     */
    function convertToUTF($text = '')
    {
        return \Warkhosh\Variable\VarStr::getTransformToEncoding($text, "UTF-8");
        //        $encoding = mb_detect_encoding($text, mb_detect_order(), false);
        //
        //        if ($encoding === "UTF-8") {
        //            $text = mb_convert_encoding($text, 'UTF-8');
        //        }
        //
        //        $out = @iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8//IGNORE", $text);
        //
        //        return $out;
    }
}

if (! function_exists('convert_entity')) {
    /**
     * Метод для работы \Warkhosh\Variable\VarStr::decodeEntities()
     * Содержит полный рекомендованный список HTML сущностей, для преобразования их в специальные символы
     *
     * @param array $matches
     * @param bool  $destroy
     * @return string
     */
    function convert_entity($matches = [], $destroy = true)
    {
        static $table = [
            'quot'     => '&#34;',
            'amp'      => '&#38;',
            'lt'       => '&#60;',
            'gt'       => '&#62;',
            'OElig'    => '&#338;',
            'oelig'    => '&#339;',
            'Scaron'   => '&#352;',
            'scaron'   => '&#353;',
            'Yuml'     => '&#376;',
            'circ'     => '&#710;',
            'tilde'    => '&#732;',
            'ensp'     => '&#8194;',
            'emsp'     => '&#8195;',
            'thinsp'   => '&#8201;',
            'zwnj'     => '&#8204;',
            'zwj'      => '&#8205;',
            'lrm'      => '&#8206;',
            'rlm'      => '&#8207;',
            'ndash'    => '&#8211;',
            'mdash'    => '&#8212;',
            'lsquo'    => '&#8216;',
            'rsquo'    => '&#8217;',
            'sbquo'    => '&#8218;',
            'ldquo'    => '&#8220;',
            'rdquo'    => '&#8221;',
            'bdquo'    => '&#8222;',
            'dagger'   => '&#8224;',
            'Dagger'   => '&#8225;',
            'permil'   => '&#8240;',
            'lsaquo'   => '&#8249;',
            'rsaquo'   => '&#8250;',
            'euro'     => '&#8364;',
            'fnof'     => '&#402;',
            'Alpha'    => '&#913;',
            'Beta'     => '&#914;',
            'Gamma'    => '&#915;',
            'Delta'    => '&#916;',
            'Epsilon'  => '&#917;',
            'Zeta'     => '&#918;',
            'Eta'      => '&#919;',
            'Theta'    => '&#920;',
            'Iota'     => '&#921;',
            'Kappa'    => '&#922;',
            'Lambda'   => '&#923;',
            'Mu'       => '&#924;',
            'Nu'       => '&#925;',
            'Xi'       => '&#926;',
            'Omicron'  => '&#927;',
            'Pi'       => '&#928;',
            'Rho'      => '&#929;',
            'Sigma'    => '&#931;',
            'Tau'      => '&#932;',
            'Upsilon'  => '&#933;',
            'Phi'      => '&#934;',
            'Chi'      => '&#935;',
            'Psi'      => '&#936;',
            'Omega'    => '&#937;',
            'alpha'    => '&#945;',
            'beta'     => '&#946;',
            'gamma'    => '&#947;',
            'delta'    => '&#948;',
            'epsilon'  => '&#949;',
            'zeta'     => '&#950;',
            'eta'      => '&#951;',
            'theta'    => '&#952;',
            'iota'     => '&#953;',
            'kappa'    => '&#954;',
            'lambda'   => '&#955;',
            'mu'       => '&#956;',
            'nu'       => '&#957;',
            'xi'       => '&#958;',
            'omicron'  => '&#959;',
            'pi'       => '&#960;',
            'rho'      => '&#961;',
            'sigmaf'   => '&#962;',
            'sigma'    => '&#963;',
            'tau'      => '&#964;',
            'upsilon'  => '&#965;',
            'phi'      => '&#966;',
            'chi'      => '&#967;',
            'psi'      => '&#968;',
            'omega'    => '&#969;',
            'thetasym' => '&#977;',
            'upsih'    => '&#978;',
            'piv'      => '&#982;',
            'bull'     => '&#8226;',
            'hellip'   => '&#8230;',
            'prime'    => '&#8242;',
            'Prime'    => '&#8243;',
            'oline'    => '&#8254;',
            'frasl'    => '&#8260;',
            'weierp'   => '&#8472;',
            'image'    => '&#8465;',
            'real'     => '&#8476;',
            'trade'    => '&#8482;',
            'alefsym'  => '&#8501;',
            'larr'     => '&#8592;',
            'uarr'     => '&#8593;',
            'rarr'     => '&#8594;',
            'darr'     => '&#8595;',
            'harr'     => '&#8596;',
            'crarr'    => '&#8629;',
            'lArr'     => '&#8656;',
            'uArr'     => '&#8657;',
            'rArr'     => '&#8658;',
            'dArr'     => '&#8659;',
            'hArr'     => '&#8660;',
            'forall'   => '&#8704;',
            'part'     => '&#8706;',
            'exist'    => '&#8707;',
            'empty'    => '&#8709;',
            'nabla'    => '&#8711;',
            'isin'     => '&#8712;',
            'notin'    => '&#8713;',
            'ni'       => '&#8715;',
            'prod'     => '&#8719;',
            'sum'      => '&#8721;',
            'minus'    => '&#8722;',
            'lowast'   => '&#8727;',
            'radic'    => '&#8730;',
            'prop'     => '&#8733;',
            'infin'    => '&#8734;',
            'ang'      => '&#8736;',
            'and'      => '&#8743;',
            'or'       => '&#8744;',
            'cap'      => '&#8745;',
            'cup'      => '&#8746;',
            'int'      => '&#8747;',
            'there4'   => '&#8756;',
            'sim'      => '&#8764;',
            'cong'     => '&#8773;',
            'asymp'    => '&#8776;',
            'ne'       => '&#8800;',
            'equiv'    => '&#8801;',
            'le'       => '&#8804;',
            'ge'       => '&#8805;',
            'sub'      => '&#8834;',
            'sup'      => '&#8835;',
            'nsub'     => '&#8836;',
            'sube'     => '&#8838;',
            'supe'     => '&#8839;',
            'oplus'    => '&#8853;',
            'otimes'   => '&#8855;',
            'perp'     => '&#8869;',
            'sdot'     => '&#8901;',
            'lceil'    => '&#8968;',
            'rceil'    => '&#8969;',
            'lfloor'   => '&#8970;',
            'rfloor'   => '&#8971;',
            'lang'     => '&#9001;',
            'rang'     => '&#9002;',
            'loz'      => '&#9674;',
            'spades'   => '&#9824;',
            'clubs'    => '&#9827;',
            'hearts'   => '&#9829;',
            'diams'    => '&#9830;',
            'nbsp'     => '&#160;',
            'iexcl'    => '&#161;',
            'cent'     => '&#162;',
            'pound'    => '&#163;',
            'curren'   => '&#164;',
            'yen'      => '&#165;',
            'brvbar'   => '&#166;',
            'sect'     => '&#167;',
            'uml'      => '&#168;',
            'copy'     => '&#169;',
            'ordf'     => '&#170;',
            'laquo'    => '&#171;',
            'not'      => '&#172;',
            'shy'      => '&#173;',
            'reg'      => '&#174;',
            'macr'     => '&#175;',
            'deg'      => '&#176;',
            'plusmn'   => '&#177;',
            'sup2'     => '&#178;',
            'sup3'     => '&#179;',
            'acute'    => '&#180;',
            'micro'    => '&#181;',
            'para'     => '&#182;',
            'middot'   => '&#183;',
            'cedil'    => '&#184;',
            'sup1'     => '&#185;',
            'ordm'     => '&#186;',
            'raquo'    => '&#187;',
            'frac14'   => '&#188;',
            'frac12'   => '&#189;',
            'frac34'   => '&#190;',
            'iquest'   => '&#191;',
            'Agrave'   => '&#192;',
            'Aacute'   => '&#193;',
            'Acirc'    => '&#194;',
            'Atilde'   => '&#195;',
            'Auml'     => '&#196;',
            'Aring'    => '&#197;',
            'AElig'    => '&#198;',
            'Ccedil'   => '&#199;',
            'Egrave'   => '&#200;',
            'Eacute'   => '&#201;',
            'Ecirc'    => '&#202;',
            'Euml'     => '&#203;',
            'Igrave'   => '&#204;',
            'Iacute'   => '&#205;',
            'Icirc'    => '&#206;',
            'Iuml'     => '&#207;',
            'ETH'      => '&#208;',
            'Ntilde'   => '&#209;',
            'Ograve'   => '&#210;',
            'Oacute'   => '&#211;',
            'Ocirc'    => '&#212;',
            'Otilde'   => '&#213;',
            'Ouml'     => '&#214;',
            'times'    => '&#215;',
            'Oslash'   => '&#216;',
            'Ugrave'   => '&#217;',
            'Uacute'   => '&#218;',
            'Ucirc'    => '&#219;',
            'Uuml'     => '&#220;',
            'Yacute'   => '&#221;',
            'THORN'    => '&#222;',
            'szlig'    => '&#223;',
            'agrave'   => '&#224;',
            'aacute'   => '&#225;',
            'acirc'    => '&#226;',
            'atilde'   => '&#227;',
            'auml'     => '&#228;',
            'aring'    => '&#229;',
            'aelig'    => '&#230;',
            'ccedil'   => '&#231;',
            'egrave'   => '&#232;',
            'eacute'   => '&#233;',
            'ecirc'    => '&#234;',
            'euml'     => '&#235;',
            'igrave'   => '&#236;',
            'iacute'   => '&#237;',
            'icirc'    => '&#238;',
            'iuml'     => '&#239;',
            'eth'      => '&#240;',
            'ntilde'   => '&#241;',
            'ograve'   => '&#242;',
            'oacute'   => '&#243;',
            'ocirc'    => '&#244;',
            'otilde'   => '&#245;',
            'ouml'     => '&#246;',
            'divide'   => '&#247;',
            'oslash'   => '&#248;',
            'ugrave'   => '&#249;',
            'uacute'   => '&#250;',
            'ucirc'    => '&#251;',
            'uuml'     => '&#252;',
            'yacute'   => '&#253;',
            'thorn'    => '&#254;',
            'yuml'     => '&#255;'
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
     * @param string $value
     * @return bool
     */
    function isEmpty($value = '')
    {
        if (is_null($value) || is_bool($value) || is_array($value) || is_object($value)) {
            return true;
        }

        $str = \Warkhosh\Variable\VarStr::getMakeString($value);
        $str = str_replace(["\n", "\t", "\r", '&nbsp;'], ['', '', '', ' '], $str);
        $str = trim($str, "\x00..\x1F");
        $str = trim($str, chr(194) . chr(160));

        return preg_match("/(\S+)/i", $str) == 0 ? true : false;
    }
}


if (! function_exists('isNotEmpty')) {
    /**
     * Проверка строки на НЕ пустое значение.
     *
     * @param string $value
     * @return bool
     */
    function isNotEmpty($value = '')
    {
        return ! isEmpty($value);
    }
}


if (! function_exists('emptyStringTo')) {
    /**
     * Возвращает $default значение если указанная строка и не пустая.
     *
     * @param string $value
     * @param mixed  $default
     * @return mixed
     */
    function emptyStringTo($value = '', $default = null)
    {
        if (is_null($value) || is_bool($value) || is_array($value) || is_object($value)) {
            return $default;
        }

        $str = \Warkhosh\Variable\VarStr::getMakeString($value);
        $str = str_replace(["\n", "\t", "\r", '&nbsp;'], ['', '', '', ' '], $str);
        $str = trim($str, "\x00..\x1F");
        $str = trim($str, chr(194) . chr(160));

        return preg_match("/(\S+)/i", $str) == 0 ? $default : $value;
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
    function itBlank($value)
    {
        if (is_null($value)) {
            return true;
        }

        // Возвращаем что значение не пустое если оно число, логического значение или массив со значениями
        if (is_numeric($value) || is_bool($value) || (is_array($value) && count($value))) {
            return false;
        }

        $value = \Warkhosh\Variable\VarStr::getMakeString($value);
        $value = str_replace(["\n", "\t", "\r", '&nbsp;'], ['', '', '', ' '], $value);
        $value = trim($value, "\x00..\x1F");

        return preg_match("/(\S+)/i", $value) == 0 ? true : false;
    }
}

if (! function_exists('getNumeralEnding')) {
    /**
     * Склонение числительных
     *
     * @param int    $number
     * @param string $str
     * @param string $ending1
     * @param string $ending24
     * @param string $ending50
     *
     * @return string
     */
    function getNumeralEnding($number = 0, $str = '', $ending1 = '', $ending24 = '', $ending50 = '')
    {
        $locale = 'ru';

        if ($locale == 'ru') {
            $number = $number % 100;

            if ($number >= 11 && $number <= 19) {
                return $str . $ending50;

            } else {
                $number = $number % 10;

                switch ($number) {
                    case 1:
                        return $str . $ending1;
                        break;
                    case 2:
                    case 3:
                    case 4:
                        return $str . $ending24;
                        break;

                    default:
                        return $str . $ending50;
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
     * Вставка в массив запись что это первый элемент
     *
     * @param array
     * @return array
     */
    function addFieldFirst($row)
    {
        return array_merge($row, ['first_child' => 1]);
    }
}


if (! function_exists('httpResponseCode')) {
    /**
     * Устанавливает по полученному коду ответ заголовка
     *
     * @param null|int $code
     * @return boolean | integer
     * @throws Exception
     */
    function httpResponseCode($code = null)
    {
        if ($code !== null) {
            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;

                case 101:
                    $text = 'Switching Protocols';
                    break;

                case 200:
                    $text = 'OK';
                    break;

                case 201:
                    $text = 'Created';
                    break;

                case 202:
                    $text = 'Accepted';
                    break;

                case 203:
                    $text = 'Non-Authoritative Information';
                    break;

                case 204:
                    $text = 'No Content';
                    break;

                case 205:
                    $text = 'Reset Content';
                    break;

                case 206:
                    $text = 'Partial Content';
                    break;

                case 300:
                    $text = 'Multiple Choices';
                    break;

                case 301:
                    $text = 'Moved Permanently';
                    break;

                case 302:
                    $text = 'Moved Temporarily';
                    break;

                case 303:
                    $text = 'See Other';
                    break;

                case 304:
                    $text = 'Not Modified';
                    break;

                case 305:
                    $text = 'Use Proxy';
                    break;

                case 400:
                    $text = 'Bad Request';
                    break;

                case 401:
                    $text = 'Unauthorized';
                    break;

                case 402:
                    $text = 'Payment Required';
                    break;

                case 403:
                    $text = 'Forbidden';
                    break;

                case 404:
                    $text = 'Not Found';
                    break;

                case 405:
                    $text = 'Method Not Allowed';
                    break;

                case 406:
                    $text = 'Not Acceptable';
                    break;

                case 407:
                    $text = 'Proxy Authentication Required';
                    break;

                case 408:
                    $text = 'Request Time-out';
                    break;

                case 409:
                    $text = 'Conflict';
                    break;

                case 410:
                    $text = 'Gone';
                    break;

                case 411:
                    $text = 'Length Required';
                    break;

                case 412:
                    $text = 'Precondition Failed';
                    break;

                case 413:
                    $text = 'Request Entity Too Large';
                    break;

                case 414:
                    $text = 'Request-URI Too Large';
                    break;

                case 415:
                    $text = 'Unsupported Media Type';
                    break;

                case 500:
                    $text = 'Internal Server Error';
                    break;

                case 501:
                    $text = 'Not Implemented';
                    break;

                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;

                case 504:
                    $text = 'Gateway Time-out';
                    break;

                case 505:
                    $text = 'HTTP Version not supported';
                    break;

                default:
                    throw new Exception('Unknown http status code "' . htmlentities($code) . '"');
            }

            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

            header($protocol . ' ' . $code . ' ' . $text);
            http_response_code($code);

            return true;
        }

        // проверка результата http_response_code(), не в окружении веб-сервера (например в CLI) он возвращает bool
        $code = ($code = http_response_code()) > 0 ? $code : 200;

        return $code;
    }
}

if (! function_exists('getMicroTime')) {
    /**
     * @return float
     */
    function getMicroTime()
    {
        [$usec, $sec] = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }
}


if (! function_exists('getPriceFormat')) {
    /**
     * Форматирует число с разделением групп
     *
     * @param float|string|integer $number - число
     * @param int                  $decimals
     * @param string               $dec_point
     * @param string               $thousands_sep
     * @return string
     */
    function getPriceFormat($number, $decimals = 0, $dec_point = ' ', $thousands_sep = ' ')
    {
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
}


if (! function_exists('getPhoneFormat')) {
    /**
     * Форматирует строку телефона в читаемый вариант
     *
     * @param string $phone
     * @param int    $code_length
     * @param int    $first_part_length
     * @param int    $second_part_length
     * @return string
     */
    function getPhoneFormat($phone = '', $code_length = 3, $first_part_length = 3, $second_part_length = 2)
    {
        $str = '';
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (mb_strlen($phone) == 10) {
            $phone = "7{$phone}";
        }

        if (mb_strlen($phone) == 11) {
            $str = "+7";
            $str .= " (" . mb_substr($phone, 1, $code_length) . ")";
            $str .= " " . mb_substr($phone, (1 + $code_length), $first_part_length);
            $str .= "-" . mb_substr($phone, (1 + $code_length + $first_part_length), $second_part_length);
            $str .= "-" . mb_substr($phone, (1 + $code_length + $first_part_length + $second_part_length), 10);
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
    function toSnakeCase($input = '')
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
     */
    function toCamelCase($input = '')
    {
        return join("", \Warkhosh\Variable\VarArray::ucfirst(explode("_", $input)));
    }
}

if (! function_exists('round_up')) {
    /**
     * Округление в большую сторону
     *
     * @note в идеале использовать BC Math Функции ( http://php.net/manual/ru/ref.bc.php )
     *
     * @param float | number | string $number
     * @param integer                 $precision
     * @return float
     */
    function round_up($number, $precision = 2)
    {
        return \Warkhosh\Variable\VarFloat::getMake($number, $precision, "upward");
    }
}

if (! function_exists('round_down')) {
    /**
     * Округление в меньшую сторону
     *
     * @note в идеале использовать BC Math Функции ( http://php.net/manual/ru/ref.bc.php )
     *
     * @param float | number | string $number
     * @param integer                 $precision
     * @return float
     */
    function round_down($number, $precision = 2)
    {
        return \Warkhosh\Variable\VarFloat::getMake($number, $precision, "downward");
    }
}

if (! function_exists('base64url_encode')) {
    /**
     * @param string $data
     * @return string
     */
    function base64url_encode($data = '')
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (! function_exists('base64url_decode')) {
    /**
     * @param string $data
     * @return string
     */
    function base64url_decode($data = '')
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (! function_exists('str_replace_once')) {
    /**
     * Замена первой найденной строки.
     *
     * @param string | array $search
     * @param string | array $replace
     * @param string         $text
     * @return string
     */
    function str_replace_once($search, $replace, string $text): string
    {
        if (is_string($search)) {
            $pos = mb_strpos($text, $search);

            if ($pos !== false) {
                // return substr_replace($text, (string)$replace, $pos, strlen($search)); шалит!
                return mb_substr($text, 0, $pos) . (string)$replace . mb_substr($text, $pos + mb_strlen($search));
            }

            return $text;
        }

        if (is_array($search) && is_bool($replace_is_string = is_string($replace))) {
            foreach ($search as $key => $searchText) {
                $replaceText = $replace_is_string ? $replace : (isset($replace[$key]) ? $replace[$key] : '');
                $text = str_replace_once($searchText, $replaceText, $text);
            }

            return $text;
        }

        trigger_error("invalid parameters passed");

        return $text;
    }
}

if (! function_exists('str_replace_last')) {
    /**
     * Замена последней найденной строки.
     *
     * @param string | array $search
     * @param string | array $replace
     * @param string         $text
     * @return string
     */
    function str_replace_last($search, $replace, string $text): string
    {
        if (is_string($search)) {
            $pos = strrpos($text, $search);

            if ($pos !== false) {
                // return substr_replace($text, (string)$replace, $pos, strlen($search)); шалит!
                return mb_substr($text, 0, $pos) . (string)$replace . mb_substr($text, $pos + mb_strlen($search));
            }

            return $text;
        }

        if (is_array($search) && is_bool($replace_is_string = is_string($replace))) {
            foreach ($search as $key => $searchText) {
                $replaceText = $replace_is_string ? $replace : (isset($replace[$key]) ? $replace[$key] : '');
                $text = str_replace_last($searchText, $replaceText, $text);
            }

            return $text;
        }

        trigger_error("invalid parameters passed");

        return $text;
    }
}

if (! function_exists('getObjectToArray')) {
    /**
     * Преобразование объектов stdClass в многомерные массивы.
     *
     * @param stdClass|array $data
     * @return array
     */
    function getObjectToArray($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map(__FUNCTION__, $data);

        } else {
            return $data;
        }
    }
}

if (! function_exists('getArrayToObject')) {
    /**
     * Функция преобразования многомерных массивов в объекты stdClass.
     *
     * @param array $data
     * @return stdClass|array
     */
    function getArrayToObject($data)
    {
        if (is_array($data)) {
            return (object)array_map(__FUNCTION__, $data);
        }

        return $data;
    }
}

if (! function_exists('getAmountMemory')) {
    /**
     * Возвращает размер памяти в к указанной единице измерения.
     *
     * @param integer $size
     * @param string  $unit
     * @param boolean $designation
     * @return string | integer | float
     */
    function getAmountMemory($size, $unit = 'b', $designation = true)
    {
        if ($unit == 'kb') {
            return round($size / 1024, 2) . ($designation ? " Kb" : '');

        } elseif ($unit == 'mb') {
            return round($size / 1048576, 2) . ($designation ? " Mb" : '');
        }

        return $size . ($designation ? " Byte" : '');
    }
}

if (! function_exists('getFileSize')) {
    /**
     * Возвращает размер файла в к указанной единице измерения.
     *
     * @param integer $size
     * @param string  $unit
     * @param boolean $designation
     * @return string
     */
    function getFileSize($size, $unit = 'b', $designation = true)
    {
        return getAmountMemory($size, $unit, $designation);
    }
}


if (! function_exists('getMemoryPeakUsage')) {
    /**
     * Возвращает пиковое значение объема памяти, выделенное PHP сценарию.
     *
     * @note при указании единицы измерения будет возвращен результат с преобразованием
     * @param string $unit
     * @param bool   $designation
     * @return integer | string
     */
    function getMemoryPeakUsage($unit = null, $designation = false)
    {
        $memory_usage = function_exists('memory_get_peak_usage') ? memory_get_peak_usage(true) : 0;

        if (is_string($unit) && ($unit === 'mb' || $unit === 'kb' || $unit === 'byte')) {
            return getAmountMemory($memory_usage, $unit, $designation);
        }

        return $memory_usage;
    }
}