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

use froq\cache\agent\{AbstractAgent, AgentInterface, AgentException};
use Error;

/**
 * File.
 * @package froq\cache\agent
 * @object  froq\cache\agent\File
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class File extends AbstractAgent implements AgentInterface
{
    /**
     * Options.
     * @var array
     */
    private $options = [
        'directory'     => null,  // Must be given in constructor.
        'serialize'     => 'php', // Only 'php' or 'json'.
        'compress'      => false, // Compress serialized data.
        'compressCheck' => false, // Verify compressed data.
    ];

    /**
     * Constructor.
     * @param array|null $options
     * @param int        $ttl
     */
    public function __construct(array $options = null, int $ttl = self::TTL)
    {
        $this->options = array_merge($this->options, ($options ?? []));

        parent::__construct(AgentInterface::NAME_FILE, $ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if (empty($this->options['directory'])) {
            throw new AgentException('Cache directory cannot be empty');
        }

        if (!is_dir($this->options['directory'])) {
            $ok =@ mkdir($this->options['directory'], 0644, true);
            if (!$ok) {
                throw new AgentException('Cannot make directory [error: %s]', ['@error']);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function has(string $key, int $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        if (!is_file($file) || !filesize($file)) {
            return false;
        }

        if ($this->options['compress'] && $this->options['compressCheck']) {
            $magic = (string) file_get_contents($file, false, null, 0, 2);
            // Check corruption (https://stackoverflow.com/a/9050274/362780).
            if (stripos($magic, "\x78\x9c") !== 0) {
                return false;
            }
        }

        $fileMTime = (int) filemtime($file);
        if ($fileMTime == 0) {
            return false;
        }
        if ($fileMTime > time() - ($ttl ?? $this->ttl)) {
            return true; // Live.
        }

        unlink($file); // Not live (do gc).

        return false;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        if ($this->has($key, $ttl)) {
            return true;
        }

        $value = $this->serialize($value);

        if ($this->options['compress']) {
            $value = gzcompress($value);
            if ($value === false) {
                return false;
            }
        }

        return (bool) file_put_contents($this->getFilePath($key), $value, LOCK_EX);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $valueDefault = null, int $ttl = null)
    {
        $value = $valueDefault;

        if ($this->has($key, $ttl)) {
            $value = (string) file_get_contents($this->getFilePath($key));

            if ($this->options['compress']) {
                $value = gzuncompress($value);
                if ($value === false) {
                    return null;
                }
            }

            $value = $this->unserialize($value);
        }

        return $value;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return @ unlink($this->getFilePath($key));
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function clear(string $subDirectory = ''): bool
    {
        $directory = $this->options['directory'];
        if ($subDirectory != '') {
            $directory .= '/'. trim($subDirectory, '/');
        }

        static $extension = '.cache';

        try {
            // Try fastest way, so far..
            exec('find '.
                escapeshellarg($directory) .' -name *'.
                escapeshellarg($extension) .' -print0 | xargs -0 rm');
        } catch (Error $e) {
            // Oh my..
            static $rmrf; if ($rmrf == null) {
                $rmrf = function ($directory) use (&$rmrf, $extension) {
                    $glob = glob($directory .'/*');
                    foreach ($glob as $path) {
                        if (is_dir($path)) {
                            $rmrf($path .'/*'); rmdir($path);
                        } elseif (is_file($path) && strpos($path, $extension)) {
                            unlink($path);
                        }
                    }
                };
            }

            $rmrf($directory);
        }

        $glob = glob($directory .'/*');

        return empty($glob);
    }

    /**
     * Get options.
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get file path.
     * @param  string $key
     * @return string
     */
    public function getFilePath(string $key): string
    {
        return sprintf('%s/%s.cache', $this->options['directory'], $key);
    }

    /**
     * Serialize.
     * @param  any $value
     * @return string
     * @throws froq\cache\agent\AgentException
     */
    private function serialize($value): string
    {
        $option = strtolower($this->options['serialize']);

        switch ($option) {
            case 'php':
                return (string) serialize($value);
            case 'json':
                return (string) json_encode($value,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
        }

        throw new AgentException('Unimplemented serialize option "%s" given', [$option]);
    }

    /**
     * Unserialize.
     * @param  string $value
     * @return any
     * @throws froq\cache\agent\AgentException
     */
    private function unserialize(string $value)
    {
        $option = strtolower($this->options['serialize']);

        switch ($option) {
            case 'php':
                return unserialize($value);
            case 'json':
                return json_decode($value);
        }

        throw new AgentException('Unimplemented serialize option "%s" given', [$option]);
    }
}
