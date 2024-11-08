<?php

namespace Warkhosh\Component\Cache;

use Warkhosh\Component\Cache\Exception\CacheException;

class CacheSerializer implements CacheSerializerInterface
{
    public const SERIALIZE = 'serialize';
    public const JSON = 'json';
    public const NONE = 'none';

    public static array $serializerMethods = [
        self::NONE => 'none',
        self::JSON => 'json',
        self::SERIALIZE => 'serialize',
    ];

    protected string $type = self::SERIALIZE;

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
     * @param string $serializer
     * @return void
     * @throws \Psr\SimpleCache\CacheException
     */
    public function setType(string $serializer): void
    {
        if (! key_exists($serializer, static::$serializerMethods)) {
            throw new CacheException("Invalid serializer type");
        }

        $this->type = $serializer;
    }

    /**
     * Creates a cache value
     *
     * @param mixed $value
     * @return string
     */
    public function getEncodeValue(mixed $value): string
    {
        if ($this->type === static::SERIALIZE) {
            $value = serialize($value);
        }

        if ($this->type === static::JSON) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * Decodes cache values to initial value
     *
     * @param mixed $value
     * @return string
     */
    public function getDecodeValue(mixed $value): string
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
