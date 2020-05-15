<?php

namespace Warkhosh\Component\Cache;

use Warkhosh\Component\Cache\Exception\CacheException;
use Warkhosh\Component\Cache\Exception\InvalidArgumentException;

/**
 * Class FileCache
 */
class FileCache extends BaseCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var string
     */
    protected $driver = "file";

    /**
     * File modification time
     *
     * @var null|int File modification time in seconds
     */
    protected $cacheExpiry = 86400;

    /**
     * @var bool Status
     */
    protected $hasCompileFileForOpCache = false;

    /**
     * @var bool
     */
    protected $opCache = false;

    /**
     * @var string
     */
    protected $path;

    /**
     * FileCache constructor
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;

        if ($this->opCache) {
            $this->hasCompileFileForOpCache = function_exists('opcache_compile_file') && ! empty(opcache_get_status());
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
        $this->validateKey($key);

        try {
            $selectKey = $this->getUpdateKeyName($key);

            $filename = $this->getFilename($selectKey);

            if (! file_exists($filename)) {
                return $default instanceof \Closure ? $default() : $default;
            }

            // Acquire a read lock (shared locked)
            $file = fopen($filename, 'rt');
            flock($file, LOCK_SH);
            $cacheValue = include $filename;
            fclose($file);

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
     *
     * @return bool True on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null)
    {
        $this->validateKey($key);

        try {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
            }

            $key = $this->getUpdateKeyName($key);
            $cacheFile = $this->getFilename($key);
            $path = dirname($cacheFile);

            // A more atomic option when creating directories
            @mkdir($path, 0755, true);

            $cacheValue = $this->createCacheValue($key, ($value instanceof \Closure ? $value() : $value), $ttl);
            $content = var_export($cacheValue, true);
            $content = "<?php return " . $content . ";";

            // Open the file for writing only. If the file does not exist, it is created.
            // If it exists, it is neither truncated, nor the call to this function fails.
            $fp = fopen($cacheFile, 'c');

            // Acquire an exclusive lock on the file while proceeding to the writing.
            if (flock($fp, LOCK_EX)) {
                ftruncate($fp, 0);
                fwrite($fp, $content);

                // opcache will only compile and cache files older than the script execution start.
                // set a date before the script execution date, then opcache will compile and cache the generated file.
                touch($cacheFile, time() - $this->getCacheExpiry());

                // This php extension is not enabled by default on windows. We must check it.
                if ($this->opCache && $this->hasCompileFileForOpCache) {
                    opcache_invalidate($cacheFile);
                    opcache_compile_file($cacheFile);
                }

                // Release the lock
                flock($fp, LOCK_UN);

            } else {
                throw new CacheException(sprintf("Couldn't get the lock for key: %s", $key));
            }

            fclose($fp);

            return true;

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
            $filename = $this->getFilename($key);

            if (file_exists($filename)) {
                unlink($filename);
            }

            return true;

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
            $iterator = new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS);

            foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isDir() && ! $path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }

            return true;

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
        $result = [];

        foreach ((array)$keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->get($key);
            } else {
                $result[$key] = $default instanceof \Closure ? $default() : $default;
            }
        }

        return $result;
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
     *
     * @return bool True if the items were successfully removed. False if there was an error
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys)
    {
        $this->validateKeys($keys);

        foreach ((array)$keys as $key) {
            $this->delete($key);
        }

        return true;
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
            $selectKey = $this->getUpdateKeyName($key);
            $filename = $this->getFilename($selectKey);

            if (! file_exists($filename)) {
                return false;
            }

            $cacheValue = include $filename;

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
     *
     * @return array Cache value
     *
     * @throws \Psr\SimpleCache\CacheException
     */
    protected function createCacheValue(string $key, $value, $ttl = null)
    {
        try {
            $value = $this->getEncodeValue($value);
            $ttl = is_null($ttl) && $this->getCacheExpiry() > 0 ? $this->getCacheExpiry() : $ttl;

            return [
                "created"   => $created = time(),
                "key"       => $key,
                "value"     => $value,
                "ttl"       => $ttl,
                "expires"   => ($ttl) ? $created + $ttl : null,
            ];
        } catch (\Throwable $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks if a value is expired
     *
     * @param null|int $expires
     * @return bool True if the value is expired
     */
    protected function isExpired(?int $expires)
    {
        if (! $expires) {
            return false;
        }

        return time() > $expires;
    }

    /**
     * Get cache filename
     *
     * @param string $key Key
     * @return string Filename
     */
    protected function getFilename(string $key)
    {
        $sha1 = sha1($key);

        return $this->path . '/' . substr($sha1, 0, 3) . '/' . substr($sha1, 3) . '.php';
    }
}