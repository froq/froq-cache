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
 * Agent Interface.
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentInterface
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
interface AgentInterface
{
    /**
     * Has.
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Set.
     * @param  string $key
     * @param  any    $value
     * @param  int    $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = Agent::TTL): bool;

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
    public function delete(string $key): bool;
}
