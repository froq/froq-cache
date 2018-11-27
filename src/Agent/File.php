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
     * Key salt.
     * @var string
     */
    private $keySalt = '';

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
     * @inheritDoc Froq\Cache\Agent\Agent
     */
    public function init(): AgentInterface
    {
        if (empty($this->directory)) {
            throw new CacheException('Cache directory cannot be empty!');
        }

        if (!is_dir($this->directory)) {
            $ok = @mkdir($this->directory, 0644, true);
            if (!$ok) {
                throw new CacheException(sprintf('Cannot make directory [%s]!',
                    strtolower(error_get_last()['message'] ?? '')));
            }
        }

        return $this;
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function has(string $key, int $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        $fileMTime = (int) @filemtime($file);
        if ($fileMTime > time() - ($ttl ?? $this->ttl)) {
            return true; // live
        }

        @unlink($file); // not live (do gc)

        return false;
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        $fileMTime = (int) @filemtime($file);
        if ($fileMTime < time() - ($ttl ?? $this->ttl)) {
            return (bool) file_put_contents($file, (string) serialize($value), LOCK_EX);
        }

        return true;
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function get(string $key, $valueDefault = null, int $ttl = null)
    {
        $value = $valueDefault;
        if ($this->has($key, $ttl)) {
            $value = unserialize((string) file_get_contents($this->getFilePath($key)));
        }

        return $value;
    }

    /**
     * @inheritDoc Froq\Cache\Agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return @unlink($this->getFilePath($key));
    }

    /**
     * Set directory.
     * @param  string $directory
     * @return void
     */
    public function setDirectory(string $directory): void
    {
        $this->directory = $directory;
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
     * Set key salt.
     * @param  string $keySalt
     * @return void
     */
    public function setKeySalt(string $keySalt): void
    {
        $this->keySalt = $keySalt;
    }

    /**
     * Get key salt.
     * @return string
     */
    public function getKeySalt(): string
    {
        return $this->keySalt;
    }

    /**
     * Get file path.
     * @param  string $key
     * @return string
     */
    public function getFilePath(string $key): string
    {
        return sprintf('%s/%s.cache', $this->directory, md5($this->keySalt . $key));
    }
}
