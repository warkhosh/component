<?php

namespace Warkhosh\Component\Cache;

use Traversable;
use Warkhosh\Component\Cache\Exception\InvalidArgumentException;

/**
 * Class BaseCache
 *
 * @author  Konstantin Egorov <ekv.programmer@gmail.com>
 * @package Warkhosh\Component\Cache
 */
abstract class BaseCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var string
     */
    protected string $driver = "array";

    /**
     * @var CacheSerializerInterface|null
     */
    protected ?CacheSerializerInterface $serializer;

    /**
     * Cache expiration in seconds
     *
     * @var int
     */
    protected int $cacheExpiry = 0;

    /**
     * A sign of a cache region
     *
     * @var string|null
     */
    protected ?string $scope = null;

    /**
     * @var mixed
     */
    protected mixed $client;

    /**
     * @var array
     */
    protected array $allowableDrivers = [
        "array" => ["name" => "Saved in array"],
        "file" => ["name" => "Saved in files"],
        "memcached" => ["name" => "Saved in memcached"],
        "redis" => ["name" => "Saved in redis"],
    ];

    /**
     * @return CacheHandler|mixed
     */
    public function getHandler(): mixed
    {
        if (empty($this->client)) {
            return new CacheHandler();
        }

        return $this->client;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return int
     */
    public function getCacheExpiry(): int
    {
        return $this->cacheExpiry;
    }

    /**
     * @param int|null $expiry
     * @return void
     */
    public function setCacheExpiry(?int $expiry): void
    {
        $this->cacheExpiry = $expiry;
    }

    /**
     * @param CacheSerializerInterface|null $serializer
     * @return void
     */
    public function setSerializerObject(?CacheSerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @return CacheSerializerInterface|null
     */
    public function getSerializerObject(): ?CacheSerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param string|null $key
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function validateKey(?string $key = null): void
    {
        if (! is_string($key)) {
            throw new InvalidArgumentException(sprintf('key %s is not a string.', $key));
        }

        foreach (['{', '}', '(', ')', '/', '@', ':'] as $needle) {
            if (str_contains($key, $needle)) {
                throw new InvalidArgumentException(sprintf('%s string is not a legal value.', $key));
            }
        }
    }

    /**
     * @param iterable $keys
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function validateKeys(iterable $keys): void
    {
        if (! is_array($keys) && ! ($keys instanceof Traversable)) {
            throw new InvalidArgumentException();
        }

        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }

    /**
     * @param iterable $values
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function validateValues(iterable $values): void
    {
        if (! is_array($values) && ! ($values instanceof Traversable)) {
            throw new InvalidArgumentException('Values must be iterable');
        }
    }

    /**
     * Creates a cache value
     *
     * @param mixed $value
     * @return string Cache value
     */
    protected function getEncodeValue(mixed $value): string
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
    protected function getDecodeValue(mixed $value): string
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
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * Установка признака области для кэша (используется для подстановки к названию кеша в начале)
     *
     * @param string|null $scope
     * @return void
     */
    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getUpdateKeyName(string $key): string
    {
        if (empty($this->getScope())) {
            return $key;
        }

        return $this->scope.preg_replace('/^(?:'.preg_quote($this->getScope(), '/').')+/u', '', $key);
    }

    /**
     * @param iterable $keys
     * @return array
     */
    protected function getUpdateKeyNames(iterable $keys): array
    {
        if (empty($this->getScope())) {
            return (array)$keys;
        }

        $newKeys = [];

        foreach ($keys as $key => $value) {
            $newKeys[$key] = $this->getScope()."{$key}";
        }

        return $newKeys;
    }
}
