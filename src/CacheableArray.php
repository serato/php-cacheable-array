<?php

namespace Serato\CacheableArray;

use Psr\SimpleCache\CacheInterface;
use ArrayIterator;
use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * An class with array-like behaviour whose data is persisted to a PSR-16 "simple cache" implementation.
 */
class CacheableArray implements ArrayAccess, Countable, IteratorAggregate
{
    /* @var CacheInterface */
    private $cache;

    /* @var string */
    private $key;

    /* @var int */
    private $ttl;

    /* @var array */
    private $data = [];

    /**
     * Constructs the object
     *
     * @param CacheInterface    $cache      A PSR-16 cache implementation
     * @param string            $key        Cache key
     * @param int               $ttl        Cache TTL (in seconds, defaults to 3600)
     */
    public function __construct(CacheInterface $cache, string $key, int $ttl = 3600)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->ttl = $ttl;
    }

    /**
     * Set the cache TTL (in seconds)
     *
     * @param int $ttl  Cache TTL
     * @return self
     */
    public function setTTL(int $ttl): self
    {
        $this->ttl = $ttl;
        return $this;
    }

    # START - Methods for ArrayAccess

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        $this->load();
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->load();
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->load();
        $this->data[$offset] = $value;
        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->load();
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
            $this->save();
        }
    }

    # END - Methods for ArrayAccess

    # START - Methods for Countable

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    # END - Methods for Countable

    # START - Methods for IteratorAggregate

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $this->load();
        return count($this->data);
    }

    # END - Methods for IteratorAggregate

    private function load()
    {
        $this->data = $this->cache->get($this->getCacheKey(), []);
    }

    private function save()
    {
        $this->cache->set($this->getCacheKey(), $this->data, $this->ttl);
    }

    private function getCacheKey()
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@'], '-', __CLASS__ . $this->key);
    }
}
