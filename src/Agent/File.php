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
            $ok =@ mkdir($directory, 0644, true);
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