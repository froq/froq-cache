<?php
/**
 * Copyright (c) 2015 Kerem Güneş
 *
 * MIT License <https://opensource.org/licenses/mit>
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
 * @object     Froq\Cache\Agent\File
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class File extends Agent
{
    /**
     * Directory.
     * @var string
     */
    private $directory;

    /**
     * Constructor.
     * @param string $directory
     * @param int    $ttl
     */
    public function __construct(string $directory = null, int $ttl = self::TTL)
    {
        $this->directory = $directory;

        parent::__construct(Cache::AGENT_FILE, $ttl);
    }

    /**
     * Init.
     * @return Froq\Cache\Agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if (empty($this->directory)) {
            throw new CacheException('Cache directory cannot be empty!');
        }

        if (!is_dir($this->directory)) {
            $ok =@ mkdir($this->directory, 0644, true);
            if (!$ok) {
                throw new CacheException(sprintf('Cannot make directory [%s]!',
                    strtolower(error_get_last()['message'] ?? '')));
            }
        }

        return $this;
    }

    /**
     * Set.
     * @param  string   $key
     * @param  any      $value
     * @param  int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $file = $this->toFile($key);
        $fileMTime =@ (int) filemtime($file);
        if ($fileMTime < time() - ($ttl ?? $this->ttl)) {
            return (bool) file_put_contents($file, $value, LOCK_EX);
        }

        return true;
    }

    /**
     * Get.
     * @param  string $key
     * @param  any    $valueDefault
     * @return any
     */
    public function get(string $key, $valueDefault = null, int $ttl = null)
    {
        $value = $valueDefault;
        $file = $this->toFile($key);
        $fileMTime =@ (int) filemtime($file);
        if ($fileMTime > time() - ($ttl ?? $this->ttl)) {
            $value = file_get_contents($file);
        } else {
            $this->delete($key); // gc
        }

        return $value;
    }

    /**
     * Delete.
     * @param  string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $file = $this->toFile($key);
        if (is_file($file)) {
            unlink($file);
            return true;
        }

        return false;
    }

    /**
     * Set directory.
     * @param  string $directory
     * @return self
     */
    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory.
     * @return ?string
     */
    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    /**
     * To file.
     * @param  string $key
     * @return string
     */
    private function toFile(string $key): string
    {
        return sprintf('%s/%s.cache', $this->directory, md5($key));
    }
}
