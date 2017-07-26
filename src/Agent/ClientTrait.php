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

/**
 * @package    Froq
 * @subpackage Froq\Cache
 * @object     Froq\Cache\Agent\ClientTrait
 * @author     Kerem Güneş <k-gun@mail.com>
 */
trait ClientTrait
{
    /**
     * Client
     * @var object
     */
    private $client;

    /**
     * Host.
     * @var string
     */
    private $host;

    /**
     * Port.
     * @var int
     */
    private $port;

    /**
     * Set client.
     * @param  object $client
     * @return self
     */
    public final function setClient($client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     * @return object
     */
    public final function getClient()
    {
        return $this->client;
    }

    /**
     * Set host.
     * @param  string $host
     * @return self
     */
    public final function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     * @return string
     */
    public final function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set port
     * @param  int $port
     * @return self
     */
    public final function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     * @return int
     */
    public final function getPort(): int
    {
        return $this->port;
    }
}
