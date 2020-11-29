<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

use froq\cache\agent\{AbstractAgent, AgentInterface, AgentException};
use Error;

/**
 * File.
 *
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
        'file'          => null,  // Set in runtime.
        'serialize'     => 'php', // Only 'php' or 'json'.
        'compress'      => false, // Compress serialized data.
        'compressCheck' => false, // Verify compressed data.
    ];

    /**
     * Constructor.
     * @param string     $id
     * @param array|null $options
     */
    public function __construct(string $id, array $options = null)
    {
        parent::__construct($id, AgentInterface::FILE, $options);

        // Filter self options only.
        $options = array_filter($options ?? [],
            fn($k) => array_key_exists($k, $this->options), 2);

        $options && $this->options = array_merge($this->options, $options);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        if (empty($this->options['directory'])) {
            throw new AgentException('Cache directory must not be empty');
        }

        if (!is_dir($this->options['directory'])) {
            $ok = mkdir($this->options['directory'], 0755, true);
            if (!$ok) {
                throw new AgentException('Cannot make directory [error: %s]', '@error');
            }
        }

        return $this;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function has(string $key, int $ttl = null): bool
    {
        $file = $this->prepareFile($key);
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

        return (bool) file_put_contents($this->prepareFile($key), $value, LOCK_EX);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $valueDefault = null, int $ttl = null)
    {
        if (!$this->has($key, $ttl)) {
            return $valueDefault;
        }

        $value = (string) file_get_contents($this->prepareFile($key));

        if ($this->options['compress']) {
            $value = gzuncompress($value);
            if ($value === false) {
                return null;
            }
        }

        return $this->unserialize($value);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return unlink($this->prepareFile($key));
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
            static $rmrf;
            $rmrf ??= function ($directory) use (&$rmrf, $extension) {
                $glob = glob($directory .'/*');
                foreach ($glob as $path) {
                    if (is_dir($path)) {
                        $rmrf($path .'/*');
                        rmdir($path);
                    } elseif (is_file($path) && strpos($path, $extension)) {
                        unlink($path);
                    }
                }
            };

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
     * Set directory.
     * @return self
     * @since  4.0
     */
    public function setDirectory(string $directory): self
    {
        $this->options['directory'] = $directory;

        return $this;
    }

    /**
     * Get directory.
     * @return string
     * @since  4.0
     */
    public function getDirectory(): string
    {
        return $this->options['directory'];
    }

    /**
     * Get file path.
     * @param  string $key
     * @return string
     * @since  4.0 Renamed as getFilePath().
     */
    private function prepareFile(string $key): string
    {
        $file = sprintf('%s/%s.cache', $this->options['directory'], $key);

        // Also cache file..
        $this->options['file'] = $file;

        return $file;
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
