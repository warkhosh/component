# Cache
A [PSR-16][1] Simple Cache Implementation.

System Requirements
-------

You need:

- **PHP >= 7.1** but the latest stable version of PHP is recommended
- the `ext-json` extension

Supported drivers
-------

`Array`, `Files`, `Memcached`, `Redis` 
:question:

File use
-------

```php
$cachePath = sys_get_temp_dir() . "/cache";

$cache = new \Warkhosh\Component\Cache\FileCache($cachePath);
```

Array use
-------

```php
$cache = new \Warkhosh\Component\Cache\ArrayCache();
```

Memcached use
-------

```php
$cache = new \Warkhosh\Component\Cache\MemcachedCache([
    "server"  => "127.0.0.1",
    "port"    => 11211,
    "weight" => 0,
    "options" => [],
]);
```

`Redis` use
-------

```php
$cache = new \Warkhosh\Component\Cache\RedisCache([
    "server"   => '127.0.0.1',
    "port"     => 6379,
    "timeout"  => 0.0,
    "reserved" => null,
    "retry"    => 0,
    "options"  => [
        \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_NONE
    ],
]);
```

Settings
-------

```php
$cache = new \Warkhosh\Component\Cache\ArrayCache();

// Cache expiration in seconds
$cache->setCacheExpiry(900);

// A sign of a cache region
$cache->setScope('<prefix_name>');

$cache->setSerializerObject(new \Warkhosh\Component\Cache\CacheSerializer('serialize'));

// Current driver
$cache->getDriver();

// Current scope name
$cache->getScope();

// Getting a serialize component
if (!is_null($serializerObject = $cache->getSerializerObject())) {
    $serializerObject->setType('json');
}
```

Usage
-------

```php
/**
 * Usage 
 */
$cache->set("foo", "bar", null);

// Caching the result from Closure for 60 seconds
$cache->set("foo", function () {
    return "bar";
}, 60);

if ($cache->has("foo")) {
    echo $cache->get("foo"); // bar
}

echo $cache->get("current_year", date("Y"));

/**
 * delete
 */

$result = $cache->delete("key");
$result = $cache->deleteMultiple(["key1", "key2", "key999"]);
$result = $cache->clear();

/**
 * Multiple usage
 */
$result = $cache->setMultiple(["foo" => "bar", "name" => "Alex"], 60); // save for 60 sec 

$values = $cache->getMultiple(["foo", "name"], "default");

$values = $cache->getMultiple(["foo", "name"], function () {
    return "default";
});

$result = $cache->deleteMultiple(["foo", "name"]);
```

[1]: http://www.php-fig.org/psr/psr-16/