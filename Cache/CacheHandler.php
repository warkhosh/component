<?php

namespace Warkhosh\Component\Cache;

use Exception;

class CacheHandler
{
    /**
     * @param string $name
     * @param array $arguments
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        throw new Exception("Еhe cache handler does not support the method {$name}");
    }

    /**
     * @param string $name
     * @param array $arguments
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        throw new Exception("Еhe cache handler does not support the static method {$name}");
    }
}
