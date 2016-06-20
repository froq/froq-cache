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
 * @subpackage Froq\Cache\Agent
 * @object     Froq\Cache\Agent\Agent
 * @author     Kerem Güneş <k-gun@mail.com>
 */
abstract class Agent implements AgentInterface
{
    /**
     * Default TTL (1 minute).
     * @const int
     */
    const DEFAULT_TTL = 3600;

    /**
     * Name.
     * @var string
     */
    private $name;

    /**
     * TTL.
     * @var int
     */
    private $ttl;

    /**
     * Constructor.
     * @param string $name
     * @param int    $ttl
     */
    public function __construct(string $name, int $ttl = self::DEFAULT_TTL)
    {
        $this->name = strtolower($name);
        $this->ttl  = $ttl;
    }

    /**
     * Set name.
     * @param  string $name
     * @return Froq\Cache\Agent\AgentInterface
     */
    final public function setName(string $name): AgentInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set TTL.
     * @param  int $ttl
     * @return Froq\Cache\Agent\AgentInterface
     */
    final public function setTtl(int $ttl): AgentInterface
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get TTL.
     * @return int
     */
    final public function getTtl(): int
    {
        return $this->ttl;
    }
}
