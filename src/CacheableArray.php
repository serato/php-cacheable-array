<?php
declare(strict_types=1);

namespace Serato\CacheableArray;

use Psr\SimpleCache\CacheInterface;
use ArrayIterator;
use ArrayAccess;
use SeekableIterator;
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

    /* @var ArrayIterator */
    private $data = null;

    /* @var bool */
    private $synced = false;

    /**
     * Constructs the object
     *
     * @param CacheInterface    $cache      A PSR-16 cache implementation
     * @param string            $key        Cache key
     * @param int               $ttl        Cache TTL (in seconds, defaults to 3600)
     *
     * @return void
     */
    public function __construct(CacheInterface $cache, string $key, int $ttl = 3600)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->ttl = $ttl;
        $this->load();
    }

    public function __destruct()
    {
        $this->save();
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
        $this->synced = false;
        return $this;
    }

    # START - Methods for ArrayAccess interface

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->data->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data->offsetSet($offset, $value);
        $this->synced = false;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->data->offsetUnset($offset);
            $this->synced = false;
        }
    }

    # END - Methods for ArrayAccess interface

    # START - Methods for IteratorAggregate interface

    /**
     * {@inheritdoc}
     */
    public function getIterator(): SeekableIterator
    {
        return $this->data;
    }

    # END - Methods for IteratorAggregate interface

    # START - Methods for Countable interface

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->data->count();
    }

    # END - Methods for Countable interface

    private function load()
    {
        if ($this->data === null) {
            $this->data = new ArrayIterator($this->cache->get($this->getCacheKey(), []));
            $this->synced = true;
        }
    }

    private function save()
    {
        if (!$this->synced) {
            $this->synced = $this->cache->set($this->getCacheKey(), $this->data->getArrayCopy(), $this->ttl);
        }
    }

    private function getCacheKey()
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@'], '-', __CLASS__ . $this->key);
    }
}
