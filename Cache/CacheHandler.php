<?php

namespace Ekv\Component\Cache;

class CacheHandler
{
    /**
     * @param string $name
     * @param array  $arguments
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        throw new \Exception("Еhe cache handler does not support the method {$name}");
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        throw new \Exception("Еhe cache handler does not support the static method {$name}");
    }
}