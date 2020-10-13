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

use froq\cache\CacheException;
use froq\cache\agent\{AgentInterface, File, Apcu, Redis, Memcached};

/**
 * Cache Factory.
 * @package froq\cache
 * @object  froq\cache\CacheFactory
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0, 4.1 Renamed as CacheFactory.
 * @static
 */
final class CacheFactory
{
    /**
     * Instances.
     * @var array<froq\cache\agent\AgentInterface>
     */
    private static array $instances = [];

    /**
     * Init.
     * @param  string     $name
     * @param  array|null $options
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\CacheException
     */
    public static function init(string $name, array $options = null): AgentInterface
    {
        $static = $options['static'] ?? true; // @default

        // Return stored instance.
        if ($static && isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $agent = null;
        switch (strtolower($name)) {
            case AgentInterface::FILE:
                $agent = new File($options);
                break;
            case AgentInterface::APCU:
                $agent = new Apcu();
                break;
            case AgentInterface::REDIS:
                $agent = new Redis();
                break;
            case AgentInterface::MEMCACHED:
                $agent = new Memcached();
                break;
            default:
                throw new CacheException('Unimplemented agent name "%s" given', [$name]);
        }

        // Set possible options.
        isset($options['host']) && $agent->setHost($options['host']);
        isset($options['port']) && $agent->setPort($options['port']);

        // Set default ttl if provided.
        if (isset($options['ttl'])) {
            $agent->setTtl($options['ttl']);
        }

        // Connect etc.
        $agent->init();

        // Store.
        if ($static) {
            self::$instances[$name] = $agent;
        }

        return $agent;
    }

    /**
     * Get instance.
     * @param  string $name
     * @return froq\cache\agent\AgentInterface
     * @throws froq\cache\CacheException
     * @since  4.1
     */
    public static function getInstance(string $name): AgentInterface
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        throw new CacheException('No agent initiated as static such "%s", call "%s::init()" '.
            'with static=true option to initiate first', [$name, self::class]);
    }
}
