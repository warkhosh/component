<?php

namespace Warkhosh\Component\Cache;

use Warkhosh\Component\Cache\Exception\CacheException;

class CacheSerializer implements CacheSerializerInterface
{
    const SERIALIZE = 'serialize';
    const JSON = 'json';
    const NONE = 'none';

    public static $serializerMethods = [
        self::NONE      => 'none',
        self::JSON      => 'json',
        self::SERIALIZE => 'serialize',
    ];

    protected $type = self::SERIALIZE;

    /**
     * @param string $type
     * @throws \Psr\SimpleCache\CacheException
     */
    public function __construct(string $type)
    {
        $this->setType($type);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return void
     * @throws \Psr\SimpleCache\CacheException
     */
    public function setType(string $type): void
    {
        if (! key_exists($type, static::$serializerMethods)) {
            throw new CacheException("Invalid serializer type");
        }

        $this->type = $type;
    }

    /**
     * Creates a cache value
     *
     * @param mixed $value
     * @return string
     */
    public function getEncodeValue($value): string
    {
        if ($this->type === static::SERIALIZE) {
            $value = serialize($value);
        }

        if ($this->type === static::JSON) {
            $value = json_encode($value);
        }

        return $value;
    }

    /**
     * Decodes cache values to initial value
     *
     * @param mixed $value
     * @return string
     */
    public function getDecodeValue($value): string
    {
        if ($this->type === static::SERIALIZE) {
            $value = unserialize($value);
        }

        if ($this->type === static::JSON) {
            $value = json_decode($value, true);
        }

        return $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type;
    }
}