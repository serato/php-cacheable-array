# Cacheable Array [![Build Status](https://travis-ci.org/serato/php-cacheable-array.svg?branch=master)](https://travis-ci.org/serato/php-cacheable-array)

[![Latest Stable Version](https://img.shields.io/packagist/v/serato/php-cacheable-array.svg)](https://packagist.org/packages/serato/php-cacheable-array)

Provides an array-like object whose objects are persisted to a PSR-16 cache

## Requirements

* PHP 7.0 or above

## Installation

Installation will usually consist of adding the library to a project's `composer.json` file:

```json
{
    "require": {
        "serato/serato/cacheable-array": "^1.0"
    }
}
```

## Usage

Create a `Serato\CacheableArray\CacheableArray` instance by providing a `Psr\SimpleCache\CacheInterface` instance and a cache key:

```php
use Serato\CacheableArray\CacheableArray;

// Use any cache that implements `Psr\SimpleCache\CacheInterface`
$cache = new \Symfony\Component\Cache\Simple\FilesystemCache;

// Create the CacheableArray instance
$ac = new CacheableArray($cache, 'my_cache_key');

// Use standard PHP syntax for accessing, counting or iterating over the CacheableArray instance
$ac['key'] = 'value';
echo $ac['key'];
echo count($ac);
foreach ($ac as $k => $v) {
    echo $v;
}
unset($ac['key']);
```

### Cache TTL

Default cache TTL is 1 hour. Cache TTL can be defined in seconds by providing a 3rd argument to the constructor or by calling the `CacheableArray::setTTL` method:

```php
use Serato\CacheableArray\CacheableArray;

// Create a CacheableArray with a cache TTL of seconds
$ac = new CacheableArray($cache, 'my_cache_key', 60);

// Change cache TTL to 300 seconds
$ac->setTTL(300);
```