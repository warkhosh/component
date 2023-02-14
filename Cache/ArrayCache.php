<?php

namespace Warkhosh\Component\Cache;

use Warkhosh\Component\Cache\Exception\CacheException;
use Warkhosh\Component\Cache\Exception\InvalidArgumentException;

/**
 * Class ArrayCache
 */
class ArrayCache extends BaseCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var string
     */
    protected $driver = "array";

    /**
     * Array cache
     *
     * @var array
     */
    protected $data = [];

    public function __construct()
    {
    }

    /**
     * @param string $key     The unique key of this item in the cache
     * @param mixed  $default Default value to return if the key does not exist
     * @return mixed          The value of the item from the cache, or $default in case of cache miss
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function get($key, $default = null)
    {
        $this->validateKey($key);

        try {
            $selectKey = $this->getUpdateKeyName($key);

            if (! key_exists($selectKey, $this->data)) {
                return $default instanceof \Closure ? $default() : $default;
            }

            $cacheValue = $this->data[$selectKey];

            if ($this->isExpired($cacheValue['expires'])) {
                $this->delete($key);

                return $default instanceof \Closure ? $default() : $default;
            }

            if (! (isset($cacheValue['value']) && key_exists('value', $cacheValue))) {
                return $default instanceof \Closure ? $default() : $default;
            }

            return $this->getDecodeValue($cacheValue['value']);

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string                 $key   The key of the item to store
     * @param mixed                  $value The value of the item to store. Must be serializable
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that
     * @return bool                         True on success and false on failure
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->validateKey($key);

        try {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $key = $this->getUpdateKeyName($key);
            $cacheValue = $this->createCacheValue($key, ($value instanceof \Closure ? $value() : $value), $ttl);
            $this->data[$key] = $cacheValue;

            return true;

        } catch (\Throwable | \Psr\SimpleCache\CacheException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $key The unique cache key of the item to delete
     * @return bool       True if the item was successfully removed. False if there was an error
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($key): bool
    {
        $this->validateKey($key);

        try {
            $key = $this->getUpdateKeyName($key);

            unset($this->data[$key]);

            return true;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return bool True on success and false on failure
     */
    public function clear(): bool
    {
        try {
            $this->data = [];

            return true;

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param iterable $keys    A list of keys that can obtained in a single operation
     * @param mixed    $default Default value to return for keys that do not exist
     * @return iterable         A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function getMultiple($keys, $default = null)
    {
        $result = [];

        foreach ((array)$keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->get($key);

            } else {
                $result[$key] = ($default instanceof \Closure ? $default() : $default);
            }
        }

        return $result;
    }

    /**
     * @param iterable               $values A list of key => value pairs for a multiple-set operation
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that
     * @return bool                          True on success and false on failure
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $this->validateValues($values);

        try {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $this->validateKeys(array_keys((array)$values));

            foreach ((array)$values as $key => $value) {
                $this->set($key, ($value instanceof \Closure ? $value() : $value), $ttl);
            }

            return true;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param iterable $keys A list of string-based keys to be deleted
     * @return bool          True if the items were successfully removed. False if there was an error
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        $this->validateKeys($keys);

        foreach ((array)$keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @param string $key The cache item key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has($key): bool
    {
        $this->validateKey($key);

        try {
            $selectKey = $this->getUpdateKeyName($key);

            if (! array_key_exists($selectKey, $this->data)) {
                return false;
            }

            $cacheValue = $this->data[$key];

            if ($this->isExpired($cacheValue['expires'])) {
                $this->delete($key);

                return false;
            }

            return true;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates a cache value object
     *
     * @param string   $key   The cache key the file is stored under
     * @param mixed    $value The data being stored
     * @param int|null $ttl   The timestamp of when the data will expire. If null, the data won't expire
     * @return array          Cache value
     * @throws \Psr\SimpleCache\CacheException
     */
    protected function createCacheValue(string $key, $value, $ttl = null): array
    {
        try {
            $value = $this->getEncodeValue($value);
            $ttl = is_null($ttl) && $this->getCacheExpiry() > 0 ? $this->getCacheExpiry() : $ttl;

            return [
                "created" => $created = time(),
                "key"     => $key,
                "value"   => $value,
                "ttl"     => $ttl,
                "expires" => ($ttl) ? $created + $ttl : null,
            ];
        } catch (\Throwable $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks if a value is expired
     *
     * @param null|int $expires
     * @return bool             True if the value is expired
     */
    protected function isExpired(?int $expires): bool
    {
        if (! $expires) {
            return false;
        }

        return time() > $expires;
    }
}