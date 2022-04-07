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
    static public function getAppName();

    /**
     * @param $className
     * @return mixed
     */
    static function getRealObject(string $className);
}