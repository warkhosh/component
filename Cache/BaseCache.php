<?php

namespace Ekv\Component\Cache;

use Ekv\Component\Cache\Exception\InvalidArgumentException;

/**
 * Class BaseCache
 *
 * @author  Konstantin Egorov <ekv.programmer@gmail.com>
 * @package Ekv\Component\Cache
 */
abstract class BaseCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var string
     */
    protected $driver = "array";

    /**
     * @var null|CacheSerializerInterface
     */
    protected $serializer;

    /**
     * Cache expiration in seconds
     *
     * @var int
     */
    protected $cacheExpiry = 0;

    /**
     * A sign of a cache region
     *
     * @var null|string
     */
    protected $scope = null;

    /**
     * @var mixed
     */
    protected $client;

    /**
     * @var array
     */
    protected $allowableDrivers = [
        "array"     => ["name" => "Saved in array"],
        "file"      => ["name" => "Saved in files"],
        "memcached" => ["name" => "Saved in memcached"],
        "redis"     => ["name" => "Saved in redis"],
    ];

    /**
     * @return mixed|CacheHandler
     */
    public function getHandler()
    {
        if (empty($this->client)) {
            return new CacheHandler();
        }

        return $this->client;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return int
     */
    public function getCacheExpiry()
    {
        return $this->cacheExpiry;
    }

    /**
     * @param int|null $expiry
     * @return void
     */
    public function setCacheExpiry(?int $expiry)
    {
        $this->cacheExpiry = $expiry;
    }

    /**
     * @param CacheSerializerInterface|null $serializer
     * @return void
     */
    public function setSerializerObject(?CacheSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return CacheSerializerInterface|null
     */
    public function getSerializerObject()
    {
        return $this->serializer;
    }

    /**
     * @param string $key
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function validateKey(string $key)
    {
        if (! is_string($key)) {
            throw new InvalidArgumentException(sprintf('key %s is not a string.', $key));
        }

        foreach (['{', '}', '(', ')', '/', '@', ':'] as $needle) {
            if (strpos($key, $needle) !== false) {
                throw new InvalidArgumentException(sprintf('%s string is not a legal value.', $key));
            }
        }
    }

    /**
     * @param iterable $keys
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function validateKeys($keys)
    {
        if (! is_array($keys) && ! ($keys instanceof \Traversable)) {
            throw new InvalidArgumentException();
        }

        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }

    /**
     * @param iterable $values
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function validateValues($values)
    {
        if (! is_array($values) && ! ($values instanceof \Traversable)) {
            throw new InvalidArgumentException('Values must be iterable');
        }
    }

    /**
     * Creates a cache value
     *
     * @param mixed $value
     * @return string Cache value
     */
    protected function getEncodeValue($value)
    {
        if (! is_null($this->serializer)) {
            return $this->serializer->getEncodeValue($value);
        }

        return $value;
    }

    /**
     * Decodes cache values to initial value
     *
     * @param mixed $value
     * @return string Cache value
     */
    protected function getDecodeValue($value)
    {
        if (! is_null($this->serializer)) {
            return $this->serializer->getDecodeValue($value);
        }

        return $value;
    }

    /**
     * Получение текущего признака области кэша (используется для подстановки к названию кеша в начале)
     *
     * @return string|null
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Установка признака области для кэша (используется для подстановки к названию кеша в начале)
     *
     * @param string|null $scope
     * @return void
     */
    public function setScope(?string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getUpdateKeyName(string $key)
    {
        if (empty($this->getScope())) {
            return $key;
        }

        return $this->scope . preg_replace('/^(?:' . preg_quote($this->getScope(), '/') . ')+/u', '', $key);
    }

    /**
     * @param iterable $keys
     * @return array
     */
    protected function getUpdateKeyNames($keys)
    {
        if (empty($this->getScope())) {
            return (array)$keys;
        }

        $newKeys = [];

        foreach ($keys as $key => $value) {
            $newKeys[$key] = $this->getScope() . "{$key}";
        }

        return $newKeys;
    }
}