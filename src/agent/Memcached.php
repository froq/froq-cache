<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * A Memcached extension wrapper class.
 *
 * @package froq\cache\agent
 * @class   froq\cache\agent\Memcached
 * @author  Kerem Güneş
 * @since   1.0
 */
class Memcached extends AbstractAgent implements AgentInterface
{
    use AgentTrait;

    /** Default host & port. */
    public const HOST = 'localhost', PORT = 11211;

    /** Default persistent key. */
    public const KEY = 'localhost';

    /** Persistent key. */
    public readonly string $key;

    /**
     * Constructor.
     *
     * @param  string     $id
     * @param  array|null $options
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $id = '', array $options = null)
    {
        if (!extension_loaded('memcached')) {
            throw AgentException::forNotFoundExtension('memcached');
        }

        $this->host = $options['host'] ?? self::HOST;
        $this->port = $options['port'] ?? self::PORT;
        $this->key  = $options['key']  ?? self::KEY;

        parent::__construct($id, 'memcached', $options);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if (!$this->host || !$this->port) {
            throw AgentException::forEmptyHostOrPort();
        }

        $this->client = new \Memcached($this->key);
        $this->client->addServer($this->host, $this->port);

        return $this;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function has(string $key): bool
    {
        $this->client->get($key);

        return $this->client->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        return $this->client->set($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->client->get($key);

        if ($this->client->getResultCode() === \Memcached::RES_NOTFOUND) {
            $value = $default;
        }

        return $value;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return $this->client->delete($key);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function clear(): bool
    {
        return $this->client->flush(0);
    }
}
