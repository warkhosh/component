<?php

namespace Warkhosh\Component\Collection\Interfaces;

interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render();
}
