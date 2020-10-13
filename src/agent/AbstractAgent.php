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

use froq\cache\agent\AgentInterface;

/**
 * Abstract Agent.
 * @package froq\cache\agent
 * @object  froq\cache\agent\AbstractAgent
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
abstract class AbstractAgent
{
    /**
     * Ttl.
     * @const int
     */
    public const TTL = 60; // 1 min.

    /**
     * Id.
     * @var string
     * @since 4.3
     */
    protected string $id;

    /**
     * Name.
     * @var string
     */
    protected string $name;

    /**
     * Static.
     * @var bool
     * @since 4.3
     */
    protected bool $static;

    /**
     * Ttl.
     * @var int
     */
    protected int $ttl;

    /**
     * Constructor.
     * @param string     $id
     * @param string     $name
     * @param array|null $options
     */
    public function __construct(string $id, string $name, array $options = null)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->static = $options['static'] ?? false;
        $this->ttl    = $options['ttl'] ?? self::TTL;
    }

    /**
     * Id.
     * @return string
     * @since  4.3
     */
    public final function id(): string
    {
        return $this->id;
    }

    /**
     * Name.
     * @return string
     * @since  4.3
     */
    public final function name(): string
    {
        return $this->name;
    }

    /**
     * Static.
     * @return string
     * @since  4.3
     */
    public final function static(): bool
    {
        return $this->static;
    }

    /**
     * Ttl.
     * @param  int|null $ttl
     * @return int
     * @since  4.3
     */
    public final function ttl(int $ttl = null): int
    {
        if ($ttl !== null) {
            $this->ttl = $ttl;
        }

        return $this->ttl;
    }

    /**
     * Set ttl.
     * @param  int $ttl
     * @return void
     */
    public final function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * Get ttl.
     * @return int
     */
    public final function getTtl(): int
    {
        return $this->ttl;
    }
}
