<?php
/**
 * MIT License <https://opensource.org/licenses/mit>
 *
 * Copyright (c) 2015 Kerem Güneş
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
declare(strict_types=1);

namespace froq\cache;

use froq\cache\{Cache, CacheException};
use froq\cache\agent\{AgentInterface, File, Apcu, Redis, Memcached};

/**
 * Cache Factory.
 * @package froq\cache
 * @object  froq\cache\CacheFactory
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.1, 4.3 Renamed as CacheFactory, added new methods.
 * @static
 */
final class CacheFactory
{
    /**
     * Instances.
     * @var array<string, froq\cache\Cache|froq\cache\agent\AgentInterface>
     */
    private static array $instances = [];

    /**
     * Instances.
     * @return array
     * @since  4.3
     */
    public static function instances(): array
    {
        return self::$instances;
    }

    /**
     * Init.
     * @param  string $id
     * @param  array  $options
     * @return froq\cache\Cache
     */
    public static function init(string $id, array $options): Cache
    {
        $key = self::prepareKey('cache', $id);

        // All cache instances are static as default (not like agent instances).
        if (!isset(self::$instances[$key])) {
            // Try to get existing agent in agent instances.
            try {
                self::$instances[$key] = new Cache($id, [], self::getAgentInstance($options['id']));
            } catch (CacheException $e) {
                self::$instances[$key] = new Cache($id, $options);
            }
        }

        return self::$instances[$key];
    }

    /**
     * Get instance.
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

        throw new CacheException('No cache initiated with "%s" name, '.
            'call "%s::init()" to initiate first', [$id, self::class]);
    }

    /**
     * Init agent.
     * @param  string $id
     * @param  array  $options
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\CacheException
     * @since  4.3
     */
    public static function initAgent(string $id, array $options): AgentInterface
    {
        $key = self::prepareKey('agent', $id);

        $static = $options['static'] ??= (bool) ($options['static'] ?? true); // @default

        // Return stored instance.
        if ($static && isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        switch (strtolower($name)) {
            case AgentInterface::FILE:
                $agent = new File($id, $options);
                break;
            case AgentInterface::APCU:
                $agent = new Apcu($id, $options);
                break;
            case AgentInterface::REDIS:
                $agent = new Redis($id, $options);
                break;
            case AgentInterface::MEMCACHED:
                $agent = new Memcached($id, $options);
                break;
            default:
                throw new CacheException('Unimplemented agent name "%s" given', [$name]);
        }

        // Connect etc (@see AgentInterface.init()).
        $agent->init();

        // Store instance.
        if ($static) {
            self::$instances[$key] = $agent;
        }

        return $agent;
    }

    /**
     * Get agent instance.
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

        throw new CacheException('No cache agent initiated with "%s" id as static, '.
            'call "%s::initAgent()" with static=true option to initiate first', [$id, self::class]);
    }

    /**
     * Key.
     * @param  string $base
     * @param  string $id
     * @return string
     * @since  4.3
     */
    private static function prepareKey(string $base, string $id): string
    {
        return $base .'@'. trim($id);
    }
}
