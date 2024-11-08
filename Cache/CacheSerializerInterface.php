<?php

namespace Warkhosh\Component\Cache;

interface CacheSerializerInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $serializer
     * @throws \Psr\SimpleCache\CacheException
     */
    public function setType(string $serializer);

    /**
     * @param mixed $value
     * @return string Cache value
     */
    public function getEncodeValue(mixed $value): string;

    /**
     * @param mixed $value
     * @return string Cache value
     */
    public function getDecodeValue(mixed $value): string;

    public function __toString();
}
