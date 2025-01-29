<?php

namespace Warkhosh\Component\Facade;

use Exception;

/**
 * Class AppFacade
 *
 * @package Ekv\Framework\Components\Facade
 */
class AppFacade implements FacadeInterface
{
    /**
     * Возвращает название класса у фасада. Если его нет, инициализирует на основе константы класса обёртки.
     *
     * @return mixed
     * @throws Exception
     */
    public static function getAppName(): mixed
    {
        $className = get_called_class(); // с php 5.5 можно через static::class!

        if (class_exists($className)) {
            if (! defined("{$className}::APP_NAME")) {
                die("Unknown facade class: {$className}!");
            }

            return $className::APP_NAME;
        }

        throw new Exception("Unknown class {$className}", E_USER_ERROR);
    }


    /**
     * Запускает класс с проверкой его на паттерн Singleton;
     *
     * @param string $className
     * @return mixed
     */
    public static function getRealObject(string $className): mixed
    {
        if (method_exists($className, 'getInstance')) {
            return $className::getInstance();
        } else {
            return new $className();
        }
    }


    /**
     * @param string $method
     * @param array $args
     *
     * @note https://www.php.net/manual/ru/language.oop5.overloading.php#object.callstatic
     *
     * @return mixed|static
     * @throws Exception
     */
    public static function __callStatic(string $method, array $args)
    {
        $className = static::getAppName();
        $instance = static::getRealObject($className);

        return $instance->$method(...$args);
    }
}
