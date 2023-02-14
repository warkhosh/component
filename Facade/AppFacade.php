<?php

namespace Warkhosh\Component\Facade;

/**
 * Class AppFacade
 *
 * @package Ekv\Framework\Components\Facade
 */
class AppFacade implements FacadeInterface
{
    protected $app;

    /**
     * Возвращает название класса у фасада. Если его нет, инициализирует на основе константы класса обёртки.
     *
     * @return mixed
     * @throws \Exception
     */
    #[\ReturnTypeWillChange]
    static public function getAppName()
    {
        $className = get_called_class(); // с php 5.5 можно через static::class!

        if (class_exists($className)) {
            if (! defined("{$className}::APP_NAME")) {
                die("Unknown facade class: {$className}!");
            }

            return $className::APP_NAME;
        }

        throw new \Exception("Unknown class {$className}", E_USER_ERROR);
    }


    /**
     * Запускает класс с проверкой его на паттерн Singleton;
     *
     * @param string $className
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    static function getRealObject(string $className)
    {
        if (method_exists($className, 'getInstance')) {
            return $className::getInstance();
        } else {
            return new $className();
        }
    }


    /**
     * @param string $method
     * @param array  $args
     *
     * @note https://www.php.net/manual/ru/language.oop5.overloading.php#object.callstatic
     *
     * @return static|mixed
     * @throws \Exception
     */
    #[\ReturnTypeWillChange]
    public static function __callStatic(string $method, array $args)
    {
        $className = static::getAppName();
        $instance = static::getRealObject($className);

        return $instance->$method(...$args);
    }
}