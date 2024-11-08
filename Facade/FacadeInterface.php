<?php

namespace Warkhosh\Component\Facade;

/**
 * Interface FacadeInterface
 *
 * @package Ekv\Framework\Components\Facade\Interfaces
 */
interface FacadeInterface
{
    /**
     * @return mixed
     */
    public static function getAppName(): mixed;

    /**
     * @param string $className
     * @return mixed
     */
    public static function getRealObject(string $className): mixed;
}
