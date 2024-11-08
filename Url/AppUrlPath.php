<?php

namespace Warkhosh\Component\Url;

use Warkhosh\Variable\VarStr;

/**
 * Class AppUrlPath
 *
 * Класс для обработки текущего урла и использования для роутинга и других проверках
 *
 * @note следует внимательно следить что-бы по коду значения не переопределялись!
 *
 * @package Ekv\Framework\Components\Page
 */
class AppUrlPath
{
    use UrlPathMethods;

    /**
     * При разборе важно не удалять не допустимые символы или пустоты между слешами как: //,
     * иначе потом в проверках не поймем что урл не допустимый.
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        $url = empty($url) ? server()->request_uri : $url;
        $url = parse_url(rawurldecode($url));

        if (isset($url['path']) && array_key_exists('path', $url)) {
            // $url['path'] = preg_replace("/[^a-zA-Z0-9\.\_\-\/]/", "", $url['path']);

            $paths = VarStr::explode('/', $url['path'], ['', ' ']);
            $types = [];

            if (count($paths) > 0) {
                foreach ($paths as $var) {
                    $types[] = is_numeric($var) ? (is_float($var) ? 'float' : ($var >= 0 ? 'num' : 'int')) : 'str';
                }

                $item = array_pop($paths); // Извлекает последний элемент массива
                $tmp = pathinfo($item);

                if (isset($tmp['filename']) && isset($tmp['extension']) && mb_strlen($tmp['extension']) > 2) {
                    $this->file = $item;
                    array_pop($types); // извлекает последний элемент типа

                } else {
                    $paths[] = $item; // возвращаем на место
                }
            }

            $this->data = array_values($paths);
            $this->types = array_values($types);

            reset($this->data);

            //if (is_null($this->file)) {
            //    $appConfig = \Warkhosh\Component\Config\AppConfig::getInstance();
            //    $this->file = (string)$appConfig->get('server.index.file');
            //}
        }
    }
}
