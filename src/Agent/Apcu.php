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

namespace Froq\Cache\Agent;

use Froq\Cache\Cache;
use Froq\Cache\CacheException;

/**
 * @package    Froq
 * @subpackage Froq\Cache
 * @object     Froq\Cache\Agent\Apcu
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Apcu extends Agent
{
    /**
     * Constructor.
     * @param  string $host
     * @param  int    $port
     * @throws Froq\Cache\CacheException
     */
    public function __construct(int $ttl = self::TTL)
    {
        if (!extension_loaded('apcu')) {
            throw new CacheException('APCu extension not found!');
        }

        parent::__construct(Cache::AGENT_APCU, $ttl);
    }

    /**
     * Init.
     * @return Froq\Cache\Agent\Agent
     */
    public function init(): AgentInterface
    {
        return $this;
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function has(string $key): bool
    {
        return apcu_exists($key);
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        return apcu_store($key, $value, ($ttl ?? $this->ttl));
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function get(string $key, $valueDefault = null)
    {
        $value = $valueDefault;
        if (apcu_exists($key)) {
            $value = apcu_fetch($key, $ok);
            if (!$ok) {
                $value = $valueDefault;
            }
        }

        return $value;
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return apcu_delete($key);
    }
}
