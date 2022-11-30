<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * A Redis extension wrapper class.
 *
 * @package froq\cache\agent
 * @class   froq\cache\agent\Redis
 * @author  Kerem Güneş
 * @since   1.0
 */
class Redis extends AbstractAgent implements AgentInterface
{
    use AgentTrait;

    /** Default host & port. */
    public const HOST = 'localhost', PORT = 6379;

    /**
     * Constructor.
     *
     * @param  string     $id
     * @param  array|null $options
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $id = '', array $options = null)
    {
        if (!extension_loaded('redis')) {
            throw new AgentException('Redis extension not loaded');
        }

        $this->host = $options['host'] ?? self::HOST;
        $this->port = $options['port'] ?? self::PORT;

        parent::__construct($id, 'redis', $options);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if (!$this->host || !$this->port) {
            throw new AgentException('Host or port cannot be empty');
        }

        $this->client = new \Redis();
        $this->client->pconnect($this->host, $this->port);

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
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        // Redis makes everything string, drops nulls as "" etc,
        // so this will keep retaining original value type.
        $value = serialize($value);

        return $this->client->set($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, mixed $default = null): mixed
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
