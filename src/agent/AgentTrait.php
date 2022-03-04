<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache\agent;

use Memcached, Redis;

/**
 * Agent Trait.
 *
 * Used by Memcached & Redis agents only to have native client properties and methods.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentTrait
 * @author  Kerem Güneş
 * @since   1.0, 5.0
 * @internal
 */
trait AgentTrait
{
    /** @var Memcached|Redis */
    private Memcached|Redis $client;

    /** @var string */
    private string $host;

    /** @var int */
    private int $port;

    /**
     * Set client.
     *
     * @param  Memcached|Redis $client
     * @return void
     */
    public function setClient(Memcached|Redis $client): void
    {
        $this->client = $client;
    }

    /**
     * Get client.
     *
     * @return Memcached|Redis
     */
    public function getClient(): Memcached|Redis|null
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
     * @return string|null
     */
    public function getHost(): string|null
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
     * @return int|null
     */
    public function getPort(): int|null
    {
        return $this->port ?? null;
    }
}
