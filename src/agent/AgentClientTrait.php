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

/**
 * Agent client trait.
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
     * @return void
     */
    public final function setClient(object $client): void
    {
        $this->client = $client;
    }

    /**
     * Get client.
     * @return object
     */
    public final function getClient(): object
    {
        return $this->client;
    }

    /**
     * Set host.
     * @param  string $host
     * @return void
     */
    public final function setHost(string $host): void
    {
        $this->host = $host;
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
     * @return void
     */
    public final function setPort(int $port): void
    {
        $this->port = $port;
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