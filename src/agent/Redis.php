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

namespace froq\cache\agent;

use froq\cache\agent\{AbstractAgent, AgentInterface, AgentException};
use Redis as _Redis;

/**
 * Redis.
 * @package froq\cache\agent
 * @object  froq\cache\agent\Redis
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Redis extends AbstractAgent implements AgentInterface
{
    /**
     * Agent client trait.
     * @object froq\cache\agent\AgentClientTrait
     */
    use AgentClientTrait;

    /**
     * Constructor.
     * @param  string $host
     * @param  int    $port
     * @param  int    $ttl
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $host = '127.0.0.1', int $port = 6379, int $ttl = self::TTL)
    {
        if (!extension_loaded('redis')) {
            throw new AgentException('Redis extension not found');
        }

        $this->host = $host;
        $this->port = $port;

        parent::__construct(AgentInterface::NAME_REDIS, $ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if ($this->host == null || $this->port == null) {
            throw new AgentException('Host or port must not be empty');
        }

        $client = new _Redis();
        $client->pconnect($this->host, $this->port);

        $this->setClient($client);

        return $this;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function has(string $key): bool
    {
        return (bool) $this->client->exists($key);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        // Redis makes everything string, drops nulls as "" etc,
        // so this will keep retaining original value type.
        $value = serialize($value);

        return $this->client->set($key, $value, ($ttl ?? $this->ttl));
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $valueDefault = null)
    {
        $value = $valueDefault;
        if ($this->has($key)) {
            $value = unserialize($this->client->get($key));
        }

        return $value;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return (bool) $this->client->del($key);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function clear(): bool
    {
        return $this->client->flushAll();
    }
}
