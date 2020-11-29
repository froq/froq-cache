<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache;

use froq\cache\{CacheException, CacheFactory};
use froq\cache\agent\AgentInterface;

/**
 * Cache.
 *
 * @package froq\cache
 * @object  froq\cache\Cache
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.1 Replaced/moved with/to CacheFactory.
 */
final class Cache
{
    /**
     * Id.
     * @var string
     * @since 4.3
     */
    private string $id;

    /**
     * Agent.
     * @var froq\cache\agent\AgentInterface
     */
    private AgentInterface $agent;

    /**
     * Constructor.
     * @param string                               $id
     * @param array                                $options
     * @param froq\cache\agent\AgentInterface|null $agent @internal @see CacheFactory.init()
     * @throws froq\cache\CacheException
     */
    public function __construct(string $id, array $options, AgentInterface $agent = null)
    {
        $this->id = $id;

        if ($agent != null) {
            $this->agent = $agent;
        } else {
            if (empty($options)) {
                throw new CacheException('No agent options given');
            } elseif (empty($options['id'])) {
                throw new CacheException('No agent id given in options');
            }

            $this->agent = CacheFactory::initAgent($options['id'], $options);
        }
    }

    /**
     * Id.
     * @return string
     * @since  4.3
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Agent.
     * @return AgentInterface
     * @since  4.2
     */
    public function agent(): AgentInterface
    {
        return $this->agent;
    }

    /**
     * Has.
     * @param  string|int|array<string|int> $key
     * @return bool
     */
    public function has($key): bool
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
     * Write.
     * @param  string|int|array<string|int> $key
     * @param  any|null                     $value
     * @param  int|null                     $ttl
     * @return bool
     */
    public function write($key, $value = null, int $ttl = null): bool
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
     * Read.
     * @param  string|int|array<string|int> $key
     * @param  any|null                     $valueDefault
     * @param  int|null                     $ttl For only "file" agent here.
     * @return any|null
     */
    public function read($key, $valueDefault = null, int $ttl = null)
    {
        $keys = $this->prepare($key, $single, __function__);

        if ($single) {
            return $this->agent->get($keys[0], $valueDefault, $ttl);
        }

        $ret = null; // Don't apply value default for empty keys.

        foreach ($keys as [$key]) {
            $ret[] = $this->agent->get($key, $valueDefault, $ttl);
        }

        return $ret;
    }

    /**
     * Remove.
     * @param  string|int|array<string|int> $key
     * @return bool
     */
    public function remove($key): bool
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
     * Flush.
     * @return bool
     */
    public function flush(): bool
    {
        return $this->agent->clear();
    }

    /**
     * Prepare.
     * @param  string|int|array  $key
     * @param  bool             &$single
     * @param  string            $func
     * @param  int|null          $argc
     * @return array
     * @throws froq\cache\CacheException
     * @todo   Use "union" type for $key argument.
     */
    private function prepare($key, ?bool &$single, string $func, int $argc = null): array
    {
        $single = is_string($key) || is_int($key);
        if ($single) {
            // Second argument is required for write().
            if (isset($argc) && $argc < 2) {
                throw new CacheException('Invalid argument count "%s" for "%s::%s()", $value '.
                    'is required when a single key given', [$argc, self::class, $func]);
            }

            $ret = [$this->prepareKey($key)];
        } else {
            if (!is_array($key)) {
                throw new CacheException('Invalid $key type "%s" for "%s::%s()", valids are: '.
                    'string, int, array<string|int>', [gettype($key), self::class, $func]);
            }

            $ret = [];

            if ($func == 'write') {
                // Generate entries for write() only.
                foreach ($key as $key => $value) {
                    if (is_string($key) || is_int($key)) {
                        $ret[] = [$this->prepareKey($key), $value];
                    }
                }
            } else {
                // Check only key types for all others.
                foreach ($key as $key) {
                    if (is_string($key) || is_int($key)) {
                        $ret[] = [$this->prepareKey($key)];
                    }
                }
            }
        }

        // Prevent empty key errors.
        $ret = array_filter($ret, fn($r) => strlen($r[0]));

        if (!$ret) {
            throw new CacheException('No valid keys/entries given for cache operations');
        }

        return $ret;
    }

    /**
     * Prepare key.
     * @param  string|int $key
     * @return string
     * @todo   Use "union" type for $key argument.
     */
    private function prepareKey($key): string
    {
        return trim((string) $key);
    }
}

