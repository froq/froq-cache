<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache\agent;

use froq\cache\agent\{AbstractAgent, AgentInterface, AgentException};
use Redis as _Redis;

/**
 * Redis.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\Redis
 * @author  Kerem Güneş
 * @since   1.0
 */
final class Redis extends AbstractAgent implements AgentInterface
{
    /** @see froq\cache\agent\AgentTrait */
    use AgentTrait;

    /**
     * Default host & port.
     * @const string, int
     * @since 4.3
     */
    public const HOST = 'localhost',
                 PORT = 6379;

    /**
     * Constructor.
     *
     * @param  string     $id
     * @param  array|null $options
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $id, array $options = null)
    {
        extension_loaded('redis') || throw new AgentException('Redis extension not found');

        $this->host = $options['host'] ?? self::HOST;
        $this->port = $options['port'] ?? self::PORT;

        parent::__construct($id, AgentInterface::REDIS, $options);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        ($this->host && $this->port) || throw new AgentException('Host or port can not be empty');

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

        return $this->client->set($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $default = null)
    {
        $value = $default;

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
