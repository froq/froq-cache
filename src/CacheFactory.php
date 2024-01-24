<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache;

use froq\cache\agent\{AgentInterface, File, Apcu, Redis, Memcached};

/**
 * A factory class for creating/storing cache or cache agent objects.
 *
 * @package froq\cache
 * @class   froq\cache\CacheFactory
 * @author  Kerem Güneş
 * @since   4.1, 4.3
 */
class CacheFactory
{
    /** Agent instances. */
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
            $agent = self::getAgentInstance($id, throw: false);

            self::$instances[$key] = new Cache($id, $options, $agent);
        }

        return self::$instances[$key];
    }

    /**
     * Get a cache instance or throw `CacheFactoryException` if no cache instance found
     * with given id.
     *
     * @param  string $id
     * @param  bool   $throw
     * @return froq\cache\Cache
     * @throws froq\cache\CacheFactoryException
     * @since  4.3
     */
    public static function getInstance(string $id, bool $throw = true): Cache|null
    {
        $key = self::prepareKey('cache', $id);

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        $throw && throw CacheFactoryException::forNoCacheWithId($id);

        return null;
    }

    /**
     * Initiate a static/dynamic agent instance with given id.
     *
     * @param  string $id
     * @param  array  $options
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\CacheFactoryException
     * @since  4.3
     */
    public static function initAgent(string $id, array $options): AgentInterface
    {
        if (empty($options['agent'])) {
            throw CacheFactoryException::forEmptyAgentOption();
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
            CacheAgent::FILE      => new File($id, $options),
            CacheAgent::APCU      => new Apcu($id, $options),
            CacheAgent::REDIS     => new Redis($id, $options),
            CacheAgent::MEMCACHED => new Memcached($id, $options),

            // Unimplemented agent option.
            default => throw CacheFactoryException::forUnimplementedAgentOption($options['agent'])
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
     * Get a static/dynamic agent instance or throw `CacheFactoryException` if no agent
     * instance found with given id.
     *
     * @param  string $id
     * @param  bool   $throw
     * @return froq\cache\agent\AgentInterface|null
     * @throws froq\cache\CacheFactoryException
     * @since  4.3
     */
    public static function getAgentInstance(string $id, bool $throw = true): AgentInterface|null
    {
        $key = self::prepareKey('agent', $id);

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        $throw && throw CacheFactoryException::forNoCacheAgentWithId($id);

        return null;
    }

    /**
     * Prepare a key with given id.
     */
    private static function prepareKey(string $base, string $id): string
    {
        return $base . '@' . trim($id);
    }
}
