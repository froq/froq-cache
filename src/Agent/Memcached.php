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
            throw new CacheException("'host' or 'port' cannot be empty!");
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
