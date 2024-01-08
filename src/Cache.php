<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache;

use froq\cache\agent\AgentInterface;

/**
 * A simple cache class for read/write operations and also removals and checks.
 *
 * @package froq\cache
 * @class   froq\cache\Cache
 * @author  Kerem Güneş
 * @since   4.1
 */
class Cache
{
    /** Identifier. */
    public readonly string $id;

    /** Agent instance. */
    public readonly AgentInterface $agent;

    /**
     * Constructor.
     *
     * @param  string                               $id
     * @param  array                                $options
     * @param  froq\cache\agent\AgentInterface|null $_agent @internal
     * @throws froq\cache\CacheException
     */
    public function __construct(string $id, array $options, AgentInterface $_agent = null)
    {
        $this->id = $id;

        if ($_agent) {
            $this->agent = $_agent;
        } else {
            if (empty($options)) {
                throw CacheException::forEmptyAgentOptions();
            } elseif (empty($options['id'] ??= $id)) {
                throw CacheException::forEmptyAgentIdOption();
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
            if (func_num_args() === 1) {
                throw CacheException::forMissingValueArgument();
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

