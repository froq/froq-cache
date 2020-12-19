<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

use froq\cache\agent\{AbstractAgent, AgentInterface, AgentException};
use Memcached as _Memcached;

/**
 * Memcached.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\Memcached
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Memcached extends AbstractAgent implements AgentInterface
{
    /**
     * Agent trait.
     * @object froq\cache\agent\AgentTrait
     */
    use AgentTrait;

    /**
     * Host & port.
     * @const string, int
     * @since 4.3
     */
    public const HOST = 'localhost',
                 PORT = 11211;

    /**
     * Constructor.
     * @param  string     $id
     * @param  array|null $options
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $id, array $options = null)
    {
        extension_loaded('memcached') || throw new AgentException('Memcached extension not found');

        $this->host = $options['host'] ?? self::HOST;
        $this->port = $options['port'] ?? self::PORT;

        parent::__construct($id, AgentInterface::MEMCACHED, $options);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        ($this->host && $this->port) || throw new AgentException('Host or port can not be empty');

        $client = new _Memcached();
        $client->addServer($this->host, $this->port);

        $this->setClient($client);

        return $this;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function has(string $key): bool
    {
        $this->client->get($key);

        return $this->client->getResultCode() === _Memcached::RES_SUCCESS;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        return $this->client->set($key, $value, ($ttl ?? $this->ttl));
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $default = null)
    {
        $value = $this->client->get($key);
        if ($this->client->getResultCode() === _Memcached::RES_NOTFOUND) {
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
