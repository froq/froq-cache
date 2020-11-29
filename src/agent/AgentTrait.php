<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

use Memcached, Redis;

/**
 * Agent Trait.
 *
 * Used by `Memcached` & `Redis` classes only to hold native client properties.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentTrait
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0, 5.0 Renamed from AgentClientTrait.
 */
trait AgentTrait
{
    /**
     * Client.
     * @var object<Memcached|Redis>
     */
    private object $client;

    /**
     * Host.
     * @var string
     */
    private string $host;

    /**
     * Port.
     * @var int
     */
    private int $port;

    /**
     * Set client.
     *
     * @param  object<Memcached|Redis> $client
     * @return void
     */
    public function setClient(object $client): void
    {
        $this->client = $client;
    }

    /**
     * Get client.
     *
     * @return ?object<Memcached|Redis>
     */
    public function getClient(): ?object
    {
        return $this->client ?? null;
    }

    /**
     * Set host.
     *
     * @param  string $host
     * @return void
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * Get host.
     *
     * @return ?string
     */
    public function getHost(): ?string
    {
        return $this->host ?? null;
    }

    /**
     * Set port.
     *
     * @param  int $port
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Get port.
     *
     * @return ?int
     */
    public function getPort(): ?int
    {
        return $this->port ?? null;
    }
}
