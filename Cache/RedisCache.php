<?php

namespace Warkhosh\Component\Cache;

use Warkhosh\Component\Cache\Exception\CacheException;
use Warkhosh\Component\Cache\Exception\InvalidArgumentException;
use DateInterval;
use DateTime;
use Closure;
use Throwable;

/**
 * Class RedisCache
 */
class RedisCache extends BaseCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var string
     */
    protected string $driver = "redis";

    /**
     * @var CacheSerializerInterface|null
     */
    protected ?CacheSerializerInterface $serializer = null;

    /**
     * Cache expiration in seconds
     *
     * @var int
     */
    protected int $cacheExpiry = 86400;

    /**
     * @var \Redis
     */
    protected mixed $client;

    /**
     * MemcachedCache constructor
     *
     * @param iterable|\Redis $config
     * @throws \Psr\SimpleCache\CacheException
     */
    public function __construct($config)
    {
        if ($config instanceof \Redis) {
            $this->client = $config;

        } elseif (is_array($config) && ! ($config instanceof \Traversable)) {
            $this->client = new \Redis();

            if ($this->client->connect($config['server'], $config['port']) === false) {
                throw new \Warkhosh\Component\Cache\Exception\CacheException("Redis connection error");
            }

            if (! key_exists(\Redis::OPT_SERIALIZER, $config['options'])) {
                $config['options'][\Redis::OPT_SERIALIZER] = \Redis::SERIALIZER_NONE;

            } elseif ($config['options'][\Redis::OPT_SERIALIZER] !== \Redis::SERIALIZER_NONE) {
                $this->serializer = null;
            }

            foreach ($config['options'] as $option => $value) {
                $this->client->setOption($option, $value);
            }

        } else {
            throw new CacheException("Error in Redis setup");
        }
    }

    /**
     * @param string $key The unique key of this item in the cache
     * @param mixed $default Default value to return if the key does not exist
     * @return mixed The value of the item from the cache, or $default in case of cache miss
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get($key, mixed $default = null): mixed
    {
        try {
            $this->validateKey($key);

            $key = $this->getUpdateKeyName($key);
            $result = $this->client->get($key);

            if ($result === false) {
                return $default instanceof Closure ? $default() : $default;
            }

            return $this->getDecodeValue($result);

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $key The key of the item to store
     * @param mixed $value The value of the item to store. Must be serializable
     * @param DateInterval|int|null $ttl Optional. The TTL value of this item. If no value is sent and
     *                                   the driver supports TTL then the library may set a default value
     *                                   for it or let the driver take care of that
     * @return bool True on success and false on failure.
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null): bool
    {
        try {
            $this->validateKey($key);

            if ($ttl instanceof DateInterval) {
                $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $ttl = is_null($ttl) && $this->getCacheExpiry() > 0 ? $this->getCacheExpiry() : (int)$ttl;

            $key = $this->getUpdateKeyName($key);
            $cacheValue = $this->getEncodeValue($value instanceof Closure ? $value() : $value);

            if ($ttl > 0) {
                return $this->client->setex($key, $ttl, $cacheValue);
            }

            return $this->client->set($key, $cacheValue);

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $key The unique cache key of the item to delete
     * @return bool True if the item was successfully removed. False if there was an error
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($key): bool
    {
        $this->validateKey($key);

        try {
            $key = $this->getUpdateKeyName($key);

            return (bool)$this->client->del([$key]);

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return bool True on success and false on failure
     */
    public function clear(): bool
    {
        try {
            if (empty($this->getScope())) {
                return $this->client->flushdb();
            }

            $this->client->del($this->client->keys($this->getScope()."*"));

            return true;

        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param iterable $keys A list of keys that can obtained in a single operation
     * @param mixed $default Default value to return for keys that do not exist
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $this->validateKeys($keys);

        try {
            if (! empty($this->getScope())) {
                $selectKeys = array_map(function ($item) {
                    return $this->getUpdateKeyName($item);
                }, (array)$keys);

            } else {
                $selectKeys = (array)$keys;
            }

            $result = array_combine((array)$keys, $this->client->mget($selectKeys));

            $result = array_map(function ($item) use ($default) {
                try {
                    if ($item === false) {
                        return $default instanceof Closure ? $default() : $default;
                    }

                    return $this->getDecodeValue($item);

                } catch (Throwable $e) {
                    return $default instanceof Closure ? $default() : $default;
                }

            }, $result);

            return $result;

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param iterable $values A list of key => value pairs for a multiple-set operation
     * @param DateInterval|int|null $ttl Optional. The TTL value of this item. If no value is sent and
     *                                   the driver supports TTL then the library may set a default value
     *                                   for it or let the driver take care of that
     * @return bool True on success and false on failure
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $this->validateValues($values);

        try {
            if ($ttl instanceof DateInterval) {
                $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $ttl = is_null($ttl) && $this->getCacheExpiry() > 0 ? $this->getCacheExpiry() : (int)$ttl;

            $this->validateKeys(array_keys((array)$values));
            $insertValues = [];

            foreach ($values as $key => $value) {
                $key = $this->getUpdateKeyName($key);
                $insertValues[$key] = $this->getEncodeValue($value instanceof Closure ? $value() : $value);
            }

            $return = true;

            if ($ttl > 0) {
                foreach ($insertValues as $key => $value) {
                    $return = $return && $this->set($key, $value, $ttl);
                }

                return $return;
            }

            return $this->client->mset($insertValues);

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param iterable $keys A list of string-based keys to be deleted
     * @return bool True if the items were successfully removed. False if there was an error
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        $this->validateKeys($keys);

        try {
            if (! empty($this->getScope())) {
                $keys = array_map(function ($item) {
                    return $this->getUpdateKeyName($item);
                }, (array)$keys);

                return $this->client->del($keys) === count((array)$keys);
            }

            return $this->client->del($keys) === count((array)$keys);

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
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
            $key = $this->getUpdateKeyName($key);

            return ($this->client->exists($key) === 1);

        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
