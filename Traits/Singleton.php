<?php

namespace Warkhosh\Component\Traits;

use Exception;

/**
 * Trait Singleton
 *
 * @package Warkhosh\Component\Traits
 */
trait Singleton
{
    private static array $instances = [];

    /**
     * Защищаем от создания через new Singleton
     */
    protected function __construct()
    {
        // ...
    }

    /**
     * Защищаем от создания через клонирование
     */
    protected function __clone()
    {
        // ...
    }

    /**
     * Защищаем от создания через unserialize
     *
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * Статический метод, управляющий доступом к экземпляру одиночки.
     *
     * @return $this
     */
    public static function getInstance(): static
    {
        $class = static::class;

        if (! isset(static::$instances[$class])) {
            static::$instances[$class] = new static();
        }

        return static::$instances[$class];
    }
}
