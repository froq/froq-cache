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

namespace Froq\Cache\Agent;

use Froq\Cache\Cache;
use Froq\Cache\CacheException;

/**
 * @package    Froq
 * @subpackage Froq\Cache
 * @object     Froq\Cache\Agent\Memcached
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Memcached extends Agent
{
    /**
     * Agent client trait.
     * @object Froq\Agent\Agent\AgentClientTrait
     */
    use AgentClientTrait;

    /**
     * Constructor.
     * @param string $host
     * @param int    $port
     * @param int    $ttl
     */
    public function __construct(string $host = '127.0.0.1', int $port = 11211, int $ttl = self::TTL)
    {
        if (!extension_loaded('memcached')) {
            throw new CacheException('Memcached extension not found!');
        }

        $this->host = $host;
        $this->port = $port;

        parent::__construct(Cache::AGENT_MEMCACHED, $ttl);
    }

    /**
     * Init.
     * @return Froq\Cache\Agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if (empty($this->host) || empty($this->port)) {
            throw new CacheException("'host' and 'port' cannot be empty!");
        }

        $client = new \Memcached();
        $client->addServer($this->host, $this->port);

        $this->setClient($client);

        return $this;
    }

    /**
     * Set.
     * @param  string   $key
     * @param  any      $value
     * @param  int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        return $this->client->set($key, $value, ($ttl ?? $this->ttl));
    }

    /**
     * Get.
     * @param  string $key
     * @param  any    $valueDefault
     * @return any
     */
    public function get(string $key, $valueDefault = null)
    {
        $value = $this->client->get($key);
        if ($this->client->getResultCode() == \Memcached::RES_NOTFOUND) {
            $value = $valueDefault;
        }

        return $value;
    }

    /**
     * Delete.
     * @param  string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->client->delete($key);
    }
}
