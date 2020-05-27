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
     * Name.
     * @var string
     */
    protected string $name;

    /**
     * Ttl.
     * @var int
     */
    protected int $ttl;

    /**
     * Constructor.
     * @param string $name
     * @param int    $ttl
     */
    public function __construct(string $name, int $ttl = self::TTL)
    {
        $this->name = $name;
        $this->ttl = $ttl;
    }

    /**
     * Set name.
     * @param  string $name
     * @return void
     */
    public final function setName(string $name): void
    {
        $this->name = strtolower($name);
    }

    /**
     * Get name.
     * @return string
     */
    public final function getName(): string
    {
        return $this->name;
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
