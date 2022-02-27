<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache;

use froq\cache\agent\{AgentInterface, File, Apcu, Redis, Memcached};

/**
 * Cache Factory.
 *
 * A factory class available for creating/storing cache or cache agent objects.
 *
 * @package froq\cache
 * @object  froq\cache\CacheFactory
 * @author  Kerem Güneş
 * @since   4.1, 4.3
 */
final class CacheFactory
{
    /** @const string */
    public const AGENT_FILE      = 'file',
                 AGENT_APCU      = 'apcu',
                 AGENT_REDIS     = 'redis',
                 AGENT_MEMCACHED = 'memcached';

    /** @var array<string, froq\cache\Cache|froq\cache\agent\AgentInterface> */
    private static array $instances = [];

    /**
     * Get instances.
     *
     * @return array
     * @since  4.3
     */
    public static function instances(): array
    {
        return self::$instances;
    }

    /**
     * Initiate a cache object and store it with given id.
     *
     * @param  string $id
     * @param  array  $options
     * @return froq\cache\Cache
     */
    public static function init(string $id, array $options): Cache
    {
        $key = self::prepareKey('cache', $id);

        // All cache instances are static as default (not like agent instances).
        if (!isset(self::$instances[$key])) {
            $options['id'] ??= $id;

            // Try to get existing agent in agent instances.
            try {
                self::$instances[$key] = new Cache($id, $options, self::getAgentInstance($id));
            } catch (CacheException) {
                self::$instances[$key] = new Cache($id, $options);
            }
        }

        return self::$instances[$key];
    }

    /**
     * Get a cache instance or throw `CacheException` if no cache instance found
     * with given id.
     *
     * @param  string $id
     * @return froq\cache\Cache
     * @throws froq\cache\CacheException
     * @since  4.3
     */
    public static function getInstance(string $id): Cache
    {
        $key = self::prepareKey('cache', $id);

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        throw new CacheException(
            'No cache initiated with id `%s`, call %s::init() to initiate first',
            [$id, self::class]
        );
    }

    /**
     * Initiate a static/dynamic agent instance with given id.
     *
     * @param  string $id
     * @param  array  $options
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\CacheException
     * @since  4.3
     */
    public static function initAgent(string $id, array $options): AgentInterface
    {
        if (empty($options['agent'])) {
            throw new CacheException('Option `agent` is empty');
        }

        $options['static'] ??= true; // @default

        // Return stored instance.
        if ($options['static']) {
            $key = self::prepareKey('agent', $id);
            if (isset(self::$instances[$key])) {
                return self::$instances[$key];
            }
        }

        $agent = match ($options['agent']) {
            self::AGENT_FILE      => new File($id, $options),
            self::AGENT_APCU      => new Apcu($id, $options),
            self::AGENT_REDIS     => new Redis($id, $options),
            self::AGENT_MEMCACHED => new Memcached($id, $options),

            default => throw new CacheException(
                'Unimplemented agent `%s`', $options['agent']
            ),
        };

        // Connect etc.
        $agent->init();

        // Store instance.
        if ($options['static']) {
            self::$instances[$key] = $agent;
        }

        return $agent;
    }

    /**
     * Get a static/dynamic agent instance or throw `CacheException` if no agent
     * instance found with given id.
     *
     * @param  string $id
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\CacheException
     * @since  4.3
     */
    public static function getAgentInstance(string $id): AgentInterface
    {
        $key = self::prepareKey('agent', $id);

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        throw new CacheException(
            'No cache agent initiated with id `%s` as static, call %s::initAgent() '.
            'with static=true option to initiate first',
            [$id, self::class]
        );
    }

    /**
     * Prepare a key with given id.
     *
     * @param  string $base
     * @param  string $id
     * @return string
     * @since  4.3
     */
    private static function prepareKey(string $base, string $id): string
    {
        return $base . '@' . trim($id);
    }
}
