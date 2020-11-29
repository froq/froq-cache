<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

/**
 * Agent Interface.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentInterface
 * @author  Kerem Güneş <k-gun@mail.com>
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
     * Init.
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\agent\AgentException
     */
    public function init(): AgentInterface;

    /**
     * Has.
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Set.
     * @param  string   $key
     * @param  any      $value
     * @param  int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = null): bool;

    /**
     * Get.
     * @param  string $key
     * @param  any    $valueDefault
     * @return any
     */
    public function get(string $key, $valueDefault = null);

    /**
     * Delete.
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Clear.
     * @return bool
     * @since  4.0
     */
    public function clear(): bool;
}
