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

use Froq\Cache\CacheException;

/**
 * @package    Froq
 * @subpackage Froq\Cache\Agent
 * @object     Froq\Cache\Agent\Memcached
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Memcached extends Agent
{
    /**
     * Client trait.
     * @object Froq\Agent\Agent\ClientTrait
     */
    use ClientTrait;

    /**
     * Default host.
     * @const string
     */
    const DEFAULT_HOST = '127.0.0.1';

    /**
     * Default port.
     * @const int
     */
    const DEFAULT_PORT = 11211;

    /**
     * Host.
     * @var string
     */
    private $host = self::DEFAULT_HOST;

    /**
     * Port
     * @var int
     */
    private $port = self::DEFAULT_PORT;

    /**
     * Constructor.
     * @param string $host
     * @param int    $port
     */
    final public function __construct(string $host = self::DEFAULT_HOST, int $port = self::DEFAULT_PORT,
        int $ttl = self::DEFAULT_TTL)
    {
        $this->host = $host;
        $this->port = $port;

        parent::__construct('memcached', $ttl);
    }

    /**
     * Set host.
     * @param  string $host
     * @return self
     */
    final public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     * @return string
     */
    final public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set port
     * @param  int $port
     * @return self
     */
    final public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     * @return int
     */
    final public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Init.
     * @return void
     */
    final public function init()
    {
        if (empty($this->host) || empty($this->port)) {
            throw new CacheException("'host' and 'port' cannot be empty!");
        }

        $client = new \Memcached();
        $client->addServer($this->host, $this->port);

        $this->setClient($client);
    }
}
