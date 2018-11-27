<?php
/**
 * Copyright (c) 2015 Kerem Güneş
 *
 * MIT License <https://opensource.org/licenses/mit>
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

namespace Froq\Cache;

use Froq\Cache\Agent\AgentInterface;
use Froq\Cache\Agent\{File, Apcu, Redis, Memcached};

/**
 * @package    Froq
 * @subpackage Froq\Cache
 * @object     Froq\Cache\Cache
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final /* static */ class Cache
{
    /**
     * Agent names.
     * @const string
     */
    public const AGENT_FILE      = 'file',
                 AGENT_APCU      = 'apcu',
                 AGENT_REDIS     = 'redis',
                 AGENT_MEMCACHED = 'memcached';

    /**
     * Instances.
     * @var array
     */
    private static $instances = [];

    /**
     * Constructor.
     */
    private function __construct()
    {}

    /**
     * Init agent.
     * @param  string     $name
     * @param  array|null $options
     * @return Froq\Cache\Agent\AgentInterface
     * @throws Froq\Cache\CacheException
     */
    public static function initAgent(string $name, array $options = null): AgentInterface
    {
        // default = true
        $once = (bool) ($options['once'] ?? true);

        if ($once && isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $agent = null;
        switch (strtolower($name)) {
            case self::AGENT_FILE:
                $agent = new File();
                if (isset($options['directory'])) {
                    $agent->setDirectory($options['directory']);
                }
                if (isset($options['keySalt'])) {
                    $agent->setKeySalt($options['keySalt']);
                }
                break;
            case self::AGENT_APCU:
                $agent = new Apcu();
                break;
            case self::AGENT_REDIS:
                $agent = new Redis();
                if (isset($options['host'])) {
                    $agent->setHost($options['host']);
                }
                if (isset($options['port'])) {
                    $agent->setPort($options['port']);
                }
                break;
            case self::AGENT_MEMCACHED:
                $agent = new Memcached();
                if (isset($options['host'])) {
                    $agent->setHost($options['host']);
                }
                if (isset($options['port'])) {
                    $agent->setPort($options['port']);
                }
                break;
            default:
                throw new CacheException("Unimplemented agent name '{$name}' given!");
        }

        // set ttl if provided
        if (isset($options['ttl'])) {
            $agent->setTtl($options['ttl']);
        }

        // connect etc.
        $agent->init();

        if ($once) {
            self::$instances[$name] = $agent;
        }

        return $agent;
    }

    /**
     * Init file agent.
     * @param  array $options
     * @return Froq\Cache\Agent\File
     * @throws Froq\Cache\CacheException
     */
    public static function initFileAgent(array $options = null): File
    {
        return self::initAgent(self::AGENT_FILE, $options);
    }

    /**
     * Init apcu agent.
     * @param  array|null $options
     * @return Froq\Cache\Agent\Apcu
     * @throws Froq\Cache\CacheException
     */
    public static function initApcuAgent(array $options = null): Apcu
    {
        return self::initAgent(self::AGENT_APCU, $options);
    }

    /**
     * Init redis agent.
     * @param  array|null $options
     * @return Froq\Cache\Agent\Redis
     * @throws Froq\Cache\CacheException
     */
    public static function initRedisAgent(array $options = null): Redis
    {
        return self::initAgent(self::AGENT_REDIS, $options);
    }

    /**
     * Init memcached agent.
     * @param  array|null $options
     * @return Froq\Cache\Agent\Memcached
     * @throws Froq\Cache\CacheException
     */
    public static function initMemcachedAgent(array $options = null): Memcached
    {
        return self::initAgent(self::AGENT_MEMCACHED, $options);
    }
}
