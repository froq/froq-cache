<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

/**
 * Agent Client Trait.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentClientTrait
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
trait AgentClientTrait
{
    /**
     * Client
     * @var object
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
     * @param  object $client
     * @return void
     */
    public function setClient(object $client): void
    {
        $this->client = $client;
    }

    /**
     * Get client.
     * @return ?object
     */
    public function getClient(): ?object
    {
        return $this->client ?? null;
    }

    /**
     * Set host.
     * @param  string $host
     * @return void
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * Get host.
     * @return ?string
     */
    public function getHost(): ?string
    {
        return $this->host ?? null;
    }

    /**
     * Set port
     * @param  int $port
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Get port.
     * @return ?int
     */
    public function getPort(): ?int
    {
        return $this->port ?? null;
    }
}
