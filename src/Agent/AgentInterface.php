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
 * @object     Froq\Cache\Agent\AgentInterface
 * @author     Kerem Güneş <k-gun@mail.com>
 */
interface AgentInterface
{
    /**
     * Init.
     * @return Froq\Cache\Agent\AgentInterface
     */
    public function init(): AgentInterface;

    /**
     * Set.
     * @param  string $key
     * @param  any    $value
     * @param  int    $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = Agent::DEFAULT_TTL): bool;

    /**
     * Get.
     * @param  string $key
     * @param  any    $valueDefault
     * @return any
     */
    public function get(string $key, $valueDefault = null);

    /**
     * Delete.
     * @return bool
     */
    public function delete(): bool;
}
