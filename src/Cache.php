<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache;

use froq\cache\{CacheException, CacheFactory};
use froq\cache\agent\AgentInterface;

/**
 * Cache.
 *
 * Represents a simple class entity which is able to read/write operations, and also to do removals
 * and validations.
 *
 * @package froq\cache
 * @object  froq\cache\Cache
 * @author  Kerem Güneş
 * @since   4.1 Replaced/moved with/to CacheFactory.
 */
final class Cache
{
    /** @var string @since 4.3 */
    private string $id;

    /** @var froq\cache\agent\AgentInterface */
    private AgentInterface $agent;

    /**
     * Constructor.
     *
     * @param  string                               $id
     * @param  array                                $options
     * @param  froq\cache\agent\AgentInterface|null $agent
     * @throws froq\cache\CacheException
     */
    public function __construct(string $id, array $options, AgentInterface $agent = null)
    {
        $this->id = $id;

        if ($agent) {
            $this->agent = $agent;
        } else {
            if (empty($options)) {
                throw new CacheException('No agent options given');
            } elseif (empty($options['id'] ??= $id)) {
                throw new CacheException('No agent id given in options');
            }

            $this->agent = CacheFactory::initAgent($options['id'], $options);
        }
    }

    /**
     * Get id property.
     *
     * @return string
     * @since  4.3
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get agent property.
     *
     * @return froq\cache\agent\AgentInterface
     * @since  4.2
     */
    public function agent(): AgentInterface
    {
        return $this->agent;
    }

    /**
     * Check whether cache contains an entry or some entries.
     *
     * @param  string|int|array $key
     * @return bool
     */
    public function has(string|int|array $key): bool
    {
        $keys = $this->prepare($key, $single, __function__);

        if ($single) {
            return $this->agent->has($keys[0]);
        }

        $ret = !!$keys; // Ensure also empty keys.

        foreach ($keys as [$key]) {
            $ret = $this->agent->has($key);
            if (!$ret) {
                break;
            }
        }

        return $ret;
    }

    /**
     * Write an entry or some entries to cache.
     *
     * @param  string|int|array $key
     * @param  any|null         $value
     * @param  int|null         $ttl
     * @return bool
     */
    public function write(string|int|array $key, $value = null, int $ttl = null): bool
    {
        $keys = $this->prepare($key, $single, __function__, func_num_args());

        if ($single) {
            return $this->agent->set($keys[0], $value, $ttl);
        }

        $ret = false; // Ensure also empty keys.

        // Must be an associative array ($value for check only).
        foreach ($keys as [$key, $value]) {
            $ret = $this->agent->set($key, $value, $ttl);
        }

        return $ret;
    }

    /**
     * Read an entry or some entries from cache.
     *
     * @param  string|int|array $key
     * @param  any|null         $default
     * @param  int|null         $ttl For only "file" agent here.
     * @return any|null
     */
    public function read(string|int|array $key, $default = null, int $ttl = null)
    {
        $keys = $this->prepare($key, $single, __function__);

        if ($single) {
            return $this->agent->get($keys[0], $default, $ttl);
        }

        $ret = null; // Don't apply value default for empty keys.

        foreach ($keys as [$key]) {
            $ret[] = $this->agent->get($key, $default, $ttl);
        }

        return $ret;
    }

    /**
     * Remove an entry or some entries from cache.
     *
     * @param  string|int|array $key
     * @return bool
     */
    public function remove(string|int|array $key): bool
    {
        $keys = $this->prepare($key, $single, __function__);

        if ($single) {
            return $this->agent->delete($keys[0]);
        }

        $ret = false; // Ensure also empty keys.

        foreach ($keys as [$key]) {
            $ret = $this->agent->delete($key);
        }

        return $ret;
    }

    /**
     * Drop all entries from cache.
     *
     * @return bool
     */
    public function flush(): bool
    {
        return $this->agent->clear();
    }

    /**
     * Prepare key/keys.
     *
     * @param  string|int|array  $key
     * @param  bool             &$single
     * @param  string            $func
     * @param  int|null          $argc
     * @return array
     * @throws froq\cache\CacheException
     */
    private function prepare(string|int|array $key, ?bool &$single, string $func, int $argc = null): array
    {
        $single = is_string($key) || is_int($key);
        if ($single) {
            // Second argument is required for write().
            if (isset($argc) && $argc < 2) {
                throw new CacheException('Invalid argument count %s for %s::%s(), value is'
                    . ' required when a single key given', [$argc, self::class, $func]);
            }

            $ret = [$this->prepareKey($key)];
        } else {
            $ret = [];

            if ($func == 'write') {
                // Generate entries for write() only.
                foreach ($key as $key => $value) {
                    $ret[] = [$this->prepareKey($key), $value];
                }
            } else {
                // Check only key types & stringify keys for all others.
                foreach ($key as $key) {
                    $ret[] = [$this->prepareKey($key)];
                }
            }
        }

        // Prevent empty key errors.
        $ret = array_filter($ret, fn($r) => strlen($r[0]));

        $ret || throw new CacheException('No valid keys/entries given for cache operation');

        return $ret;
    }

    /**
     * Prepare a key.
     *
     * @param  string|int $key
     * @return string
     */
    private function prepareKey(string|int $key): string
    {
        return trim((string) $key);
    }
}

