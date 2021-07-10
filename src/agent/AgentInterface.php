<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache\agent;

/**
 * Agent Interface.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentInterface
 * @author  Kerem Güneş
 * @since   1.0
 */
interface AgentInterface
{
    /**
     * Names.
     * @const string
     */
    public const FILE      = 'file',
                 APCU      = 'apcu',
                 REDIS     = 'redis',
                 MEMCACHED = 'memcached';

    /**
     * Initialize a cache agent.
     *
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\agent\AgentException
     */
    public function init(): AgentInterface;

    /**
     * Check whether any item was cached with given key.
     *
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Put an item to cache with given key/value and optionally given TTL.
     *
     * @param  string   $key
     * @param  any      $value
     * @param  int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = null): bool;

    /**
     * Fetch an item from cache with given key or return default param when not exists.
     *
     * @param  string   $key
     * @param  any|null $default
     * @return any|null
     */
    public function get(string $key, $default = null);

    /**
     * Drop an item from cache with given key.
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Drop all items from cache.
     *
     * @return bool
     * @since  4.0
     */
    public function clear(): bool;
}
