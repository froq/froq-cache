<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
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
final class Cache
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
     * Init.
     * @param  string     $name
     * @param  array|null $options
     * @return Froq\Cache\Agent\AgentInterface
     */
    public static function init(string $name, array $options = null): AgentInterface
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
                break;
            case self::AGENT_APCU:
                $agent = new Apcu();
                break;
            case self::AGENT_REDIS:
                $agent = new Redis();
                if (isset($options['host'])) $agent->setHost($options['host']);
                if (isset($options['port'])) $agent->setPort($options['port']);
                break;
            case self::AGENT_MEMCACHED:
                $agent = new Memcached();
                if (isset($options['host'])) $agent->setHost($options['host']);
                if (isset($options['port'])) $agent->setPort($options['port']);
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
     * Init file.
     * @param  array $options
     * @return Froq\Cache\Agent\File
     */
    public static function initFile(array $options = null): File
    {
        return self::init(self::AGENT_FILE, $options);
    }

    /**
     * Init apcu.
     * @param  array|null $options
     * @return Froq\Cache\Agent\Apcu
     */
    public static function initApcu(array $options = null): Apcu
    {
        return self::init(self::AGENT_APCU, $options);
    }

    /**
     * Init redis.
     * @param  array|null $options
     * @return Froq\Cache\Agent\Redis
     */
    public static function initRedis(array $options = null): Redis
    {
        return self::init(self::AGENT_REDIS, $options);
    }

    /**
     * Init memcached.
     * @param  array|null $options
     * @return Froq\Cache\Agent\Memcached
     */
    public static function initMemcached(array $options = null): Memcached
    {
        return self::init(self::AGENT_MEMCACHED, $options);
    }
}
