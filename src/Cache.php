<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache;

use froq\cache\agent\AgentInterface;

/**
 * A simple cache class for read/write operations and also removals and checks.
 *
 * @package froq\cache
 * @object  froq\cache\Cache
 * @author  Kerem Güneş
 * @since   4.1
 */
final class Cache
{
    /** @var string */
    public readonly string $id;

    /** @var froq\cache\agent\AgentInterface */
    public readonly AgentInterface $agent;

    /**
     * Constructor.
     *
     * @param  string                               $id
     * @param  array                                $options
     * @param  froq\cache\agent\AgentInterface|null $agent @internal
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
     * Check one/many items on cache.
     *
     * @param  string|int|array<string|int> $key
     * @param  int|null                     $ttl For "file" agent only.
     * @return bool
     * @since  4.1, 6.0
     */
    public function has(string|int|array $key, int $ttl = null): bool
    {
        // Multi items.
        if (is_array($key)) {
            $ret = false;
            foreach ($key as $key) {
                $ret = $this->agent->has((string) $key, $ttl);
                if (!$ret) {
                    break;
                }
            }
        } else {
           $ret = $this->agent->has((string) $key, $ttl);
        }

        return $ret;
    }

    /**
     * Set one/many items on cache.
     *
     * @param  string|int|array<string|int> $key
     * @param  mixed|null                   $value
     * @param  int|null                     $ttl For "file" agent only.
     * @return bool
     * @throws froq\cache\CacheException
     * @since  4.1, 6.0
     */
    public function set(string|int|array $key, mixed $value = null, int $ttl = null): bool
    {
        // Multi items.
        if (is_array($key)) {
            $ret = false;
            foreach ($key as $key => $value) {
                $ret = $this->agent->set((string) $key, $value, $ttl);
            }
        } else {
            // Sadly $ttl is overriding this hedge..
            if (func_num_args() == 1) {
                throw new CacheException('Argument $value is not given');
            }

            $ret = $this->agent->set((string) $key, $value, $ttl);
        }

        return $ret;
    }

    /**
     * Get one/many items from cache.
     *
     * @param  string|int|array<string|int> $key
     * @param  mixed|null                   $default
     * @param  int|null                     $ttl For "file" agent only.
     * @return mixed
     * @since  4.1, 6.0
     */
    public function get(string|int|array $key, mixed $default = null, int $ttl = null): mixed
    {
        // Multi items.
        if (is_array($key)) {
            $ret = [];
            foreach ($key as $key) {
                $ret[] = $this->agent->get((string) $key, $default, $ttl);
            }
        } else {
            $ret = $this->agent->get((string) $key, $default, $ttl);
        }

        return $ret;
    }

    /**
     * Delete one/many items from cache.
     *
     * @param  string|int|array<string|int> $key
     * @return bool
     * @since  4.1, 6.0
     */
    public function delete(string|int|array $key): bool
    {
        // Multi items.
        if (is_array($key)) {
            $ret = false;
            foreach ($key as $key) {
                $ret = $this->agent->delete((string) $key);
            }
        } else {
            $ret = $this->agent->delete((string) $key);
        }

        return $ret;
    }

    /**
     * Clear all cache.
     *
     * @return bool
     * @since  4.1, 6.0
     */
    public function clear(): bool
    {
        return $this->agent->clear();
    }
}

