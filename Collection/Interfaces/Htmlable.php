<?php

namespace Ekv\Component\Collection\Interfaces;

interface Htmlable
{
    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml();
}
