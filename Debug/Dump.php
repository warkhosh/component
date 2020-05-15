<?php

namespace Warkhosh\Component\Debug;


/**
 * Class Dump
 *
 * @package Warkhosh\Component\Debug
 */
class Dump
{
    /**
     * Оформление вывода
     *
     * @var array
     */
    public static $wrapper = [
        "layout" => '<pre style="border:1px solid #900; margin:5px; padding:3px; font-size:10pt; white-space: pre;">%s</pre>',
        "title"  => '<div style="background-color:#990000; color:#FFF; padding:2px;"><strong>%s</strong></div>',
        "type"   => '<div style="padding:5px;" align="left">[<strong>%s</strong>]</div>',
        "value"  => '<div style="padding:5px;" align="left">%s</div>',
    ];

    /**
     * Вывод полученого параметра с оформлением.
     *
     * @param string $value = 'test'
     * @param string $title = 'PRINT PARAM'
     * @return string
     */
    static public function get($value = 'test', $title = 'PRINT PARAM')
    {
        if (is_bool($value)) {
            $value = ($value === true ? "TRUE" : "FALSE");
        }

        $valueType = gettype($value);
        // $list = get_debug_called_list(ret_debugger());
        $value = (is_null($value) ? "Null" : $value);
        $value = print_r($value, true);
        $value = htmlentities($value, ENT_QUOTES, 'UTF-8');

        // Decor
        $title = sprintf(static::$wrapper['title'], $title);
        $type = sprintf(static::$wrapper['type'], $valueType);
        $value = sprintf(static::$wrapper['value'], $value);

        return sprintf(PHP_EOL . static::$wrapper['layout'] . PHP_EOL, $title . $type . $value);
    }
}