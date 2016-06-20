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
use Froq\Cache\Agent\{Memcached};

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
    const AGENT_APC = 'apc',
          AGENT_FILE = 'file',
          AGENT_REDIS = 'redis',
          AGENT_MEMCACHED = 'memcached';

    /**
     * Constructor.
     */
    final private function __construct()
    {}

    /**
     * Init agent.
     * @param  string $name
     * @param  array  $options
     * @return Froq\Cache\Agent\AgentInterface
     */
    final public function init(string $name, array $options = null): AgentInterface
    {
        $agent = null;
        switch (strtolower($name)) {
            // only memcached for now
            case self::AGENT_MEMCACHED:
                $agent = new Memcached();
                break;
            default:
                throw new CacheException("Unimplemented agent '{$name}' given!");
        }

        // chec/set options
        if ($agent != null) {
            isset($options['host'])
                && $agent->setHost($options['host']);
            isset($options['port'])
                && $agent->setPort($options['port']);
            isset($options['ttl'])
                && $agent->setTtl($options['ttl']);
        }

        return $agent;
    }

    /**
     * Init memcached.
     * @param  array $options
     * @return Froq\Cache\Agent\Memcached
     */
    final public function initMemcached(array $options = null): Memcached
    {
        return $this->init(self::AGENT_MEMCACHED, $options);
    }
}
