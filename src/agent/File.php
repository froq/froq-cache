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

use froq\cache\{Cache, CacheException};

/**
 * File.
 * @package froq\cache\agent
 * @object  froq\cache\agent\File
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class File extends Agent
{
    /**
     * Options.
     * @var array
     */
    private $options = [
        'directory' => null,
        'serialize' => 'php' // php or json
    ];

    /**
     * Constructor.
     * @param array|null $options
     * @param int        $ttl
     */
    public function __construct(array $options = null, int $ttl = self::TTL)
    {
        $this->options = array_merge($this->options, ($options ?? []));

        parent::__construct(Cache::AGENT_FILE, $ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\Agent
     */
    public function init(): AgentInterface
    {
        if (empty($this->options['directory'])) {
            throw new CacheException('Cache directory cannot be empty');
        }

        if (!is_dir($this->options['directory'])) {
            $ok =@ mkdir($this->options['directory'], 0644, true);
            if (!$ok) {
                throw new CacheException(sprintf('Cannot make directory, error[%s]',
                    error_get_last()['message'] ?? 'Unknown'));
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
        $fileMTime =@ (int) filemtime($file);
        if ($fileMTime == 0) {
            return false;
        }

        if ($fileMTime > time() - ($ttl ?? $this->ttl)) {
            return true; // live
        }

        @unlink($file); // not live (do gc)

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

        return (bool) file_put_contents($this->getFilePath($key), $this->serialize($value), LOCK_EX);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $valueDefault = null, int $ttl = null)
    {
        $value = $valueDefault;
        if ($this->has($key, $ttl)) {
            $value = $this->unserialize((string) file_get_contents($this->getFilePath($key)));
        }

        return $value;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return @unlink($this->getFilePath($key));
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
        return sprintf('%s/%s.cache', $this->options['directory'], md5($key));
    }

    /**
     * Serialize.
     * @param  any $value
     * @return string
     * @throws froq\cache\CacheException
     */
    private function serialize($value): string
    {
        $serializeOption = $this->options['serialize'];
        if ($serializeOption == 'php') {
            return (string) serialize($value);
        }
        if ($serializeOption == 'json') {
            return (string) json_encode($value);
        }

        throw new CacheException("Unimplemented serialize option '{$serializeOption}' given");
    }

    /**
     * Unserialize.
     * @param  string $value
     * @return any
     * @throws froq\cache\CacheException
     */
    private function unserialize(string $value)
    {
        $serializeOption = $this->options['serialize'];
        if ($serializeOption == 'php') {
            return unserialize($value);
        }
        if ($serializeOption == 'json') {
            return json_decode($value);
        }

        throw new CacheException("Unimplemented serialize option '{$serializeOption}' given");
    }
}
