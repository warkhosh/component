<?php

namespace Ekv\Component\Cache;


use Ekv\Component\Cache\Exception\InvalidArgumentException;

/**
 * Class MemcachedCache
 */
class MemcachedCache extends BaseCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var string
     */
    protected $driver = "memcached";

    /**
     * Cache expiration in seconds
     *
     * @var null|int
     */
    protected $cacheExpiry = 86400;

    /**
     * @var \Memcached
     */
    protected $client;

    /**
     * MemcachedCache constructor
     *
     * @param \Memcached|iterable $config
     * @throws \Psr\SimpleCache\CacheException
     */
    public function __construct($config)
    {
        if ($config instanceof \Memcached) {
            $this->client = $config;

        } elseif (is_array($config) && ! ($config instanceof \Traversable)) {
            $this->client = new \Memcached();
            $this->client->addServer($config['server'], $config['port'], $config['weight']);

            foreach ($config['options'] as $option => $value) {
                $this->client->setOption($option, $value);
            }

        } else {
            throw new \Ekv\Component\Cache\Exception\CacheException("Error in Memcached setup");

        }
    }

    /**
     * @param string $key     The unique key of this item in the cache
     * @param mixed  $default Default value to return if the key does not exist
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        try {
            $this->validateKey($key);

            $key = $this->getUpdateKeyName($key);
            $result = $this->client->get($key);

            if ($result === false) {
                $resultCode = $this->client->getResultCode();

                if ($resultCode === \Memcached::RES_NOTFOUND) {
                    return $default instanceof \Closure ? $default() : $default;
                }

                $this->checkException($resultCode, $key);
            }

            return $this->getDecodeValue($result);

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
     *
     * @return bool True on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null)
    {
        try {
            $this->validateKey($key);

            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $ttl = is_null($ttl) && $this->getCacheExpiry() > 0 ? $this->getCacheExpiry() : (int)$ttl;

            $key = $this->getUpdateKeyName($key);
            $cacheValue = $this->getEncodeValue($value instanceof \Closure ? $value() : $value);

            $result = $this->client->set($key, $cacheValue, $ttl);

            if ($result === false) {
                $this->checkException($this->client->getResultCode(), $key);
            }

            return $result;

        } catch (\Throwable | \Psr\SimpleCache\CacheException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $key The unique cache key of the item to delete
     *
     * @return bool True if the item was successfully removed. False if there was an error
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($key)
    {
        $this->validateKey($key);

        try {
            $key = $this->getUpdateKeyName($key);

            return $this->client->delete($key);

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return bool True on success and false on failure
     */
    public function clear()
    {
        try {
            return $this->client->flush();

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param iterable $keys    A list of keys that can obtained in a single operation
     * @param mixed    $default Default value to return for keys that do not exist
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
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

            $result = $this->client->getMulti($selectKeys, \Memcached::GET_PRESERVE_ORDER);

            if ($result === false) {
                return array_fill(0, count((array)$keys), $default);
            }

            $result = array_map(function ($item) use ($default) {
                try {
                    if ($item === false) {
                        return $default instanceof \Closure ? $default() : $default;
                    }

                    return $this->getDecodeValue($item);

                } catch (\Throwable $e) {
                    return $default instanceof \Closure ? $default() : $default;
                }

            }, $result);

            return $result;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param iterable               $values A list of key => value pairs for a multiple-set operation
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that
     *
     * @return bool True on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
    {
        $this->validateValues($values);

        try {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $ttl = is_null($ttl) && $this->getCacheExpiry() > 0 ? $this->getCacheExpiry() : (int)$ttl;

            $this->validateKeys(array_keys((array)$values));
            $insertValues = [];

            foreach ($values as $key => $value) {
                $key = $this->getUpdateKeyName($key);
                $insertValues[$key] = $this->getEncodeValue($value instanceof \Closure ? $value() : $value);
            }

            $result = $this->client->setMulti((array)$insertValues, $ttl);

            if ($result === false) {
                $resultCode = $this->client->getResultCode();
                $this->checkException($resultCode);
            }

            return $result;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param iterable $keys A list of string-based keys to be deleted
     *
     * @return bool True if the items were successfully removed. False if there was an error
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys)
    {
        $this->validateKeys($keys);

        try {
            if (! empty($this->getScope())) {
                $keys = array_map(function ($item) {
                    return $this->getUpdateKeyName($item);
                }, (array)$keys);

                $result = $this->client->deleteMulti($keys);

            } else {
                $result = $this->client->deleteMulti((array)$keys);
            }

            // Memcached returns an array of key => success, must format it to match psr-16 interface
            if (is_array($result)) {
                foreach ($result as $success) {
                    if (! $success) {
                        return false;
                    }
                }
            }

            return true;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $key The cache item key
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has($key)
    {
        $this->validateKey($key);

        try {
            $key = $this->getUpdateKeyName($key);
            $result = $this->client->get($key);

            if ($result === false && $this->client->getResultCode() === \Memcached::RES_NOTFOUND) {
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks the result of memcached for a bad key provided, this must be used after a memcached operation
     *
     * @param int    $resultCode
     * @param string $key
     * @throws \Exception
     */
    private function checkException($resultCode, ?string $key = null)
    {
        switch ($resultCode) {
            case \Memcached::RES_BAD_KEY_PROVIDED:
                $message = sprintf('Invalid key %s provided, Message: %s', $key, $this->client->getResultMessage());
                break;

            default:
                $message = sprintf('Message: %s', $this->client->getResultMessage());
        }

        throw new \Exception($message);
    }
}