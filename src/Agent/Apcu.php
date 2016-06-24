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

use Froq\Cache\Cache;
use Froq\Cache\CacheException;

/**
 * @package    Froq
 * @subpackage Froq\Cache\Agent
 * @object     Froq\Cache\Agent\Apcu
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Apcu extends Agent
{
    /**
     * Constructor.
     * @param string $host
     * @param int    $port
     */
    final public function __construct(int $ttl = self::DEFAULT_TTL)
    {
        if (!extension_loaded('apcu')) {
            throw new CacheException("APCu extension not found!");
        }

        parent::__construct(Cache::AGENT_APCU, $ttl);
    }

    /**
     * Init.
     * @return Froq\Cache\Agent\AgentInterface
     */
    final public function init(): AgentInterface
    {
        return $this;
    }

    /**
     * Set.
     * @param  string   $key
     * @param  any      $value
     * @param  int|null $ttl
     * @return bool
     */
    final public function set(string $key, $value, int $ttl = null): bool
    {
        return apcu_store($key, $value, ($ttl ?? $this->ttl));
    }

    /**
     * Get.
     * @param  string $key
     * @param  any    $valueDefault
     * @return any
     */
    final public function get(string $key, $valueDefault = null)
    {
        $value = apcu_fetch($key, $ok);
        if (!$ok) {
            $value = $valueDefault;
        }

        return $value;
    }

    /**
     * Delete.
     * @param  string $key
     * @return bool
     */
    final public function delete(string $key): bool
    {
        return apcu_delete($key);
    }
}
