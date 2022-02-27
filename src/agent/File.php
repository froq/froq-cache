<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
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
 * @author  Kerem Güneş
 * @since   1.0
 */
final class File extends AbstractAgent implements AgentInterface
{
    /** @var string */
    private string $file;

    /** @var array */
    private array $options = [
        'directory'     => null,  // Must be given in constructor.
        'serialize'     => null,  // Only 'php' or 'json'.
        'compress'      => false, // Compress data.
        'compressCheck' => false, // Verify compressed data.
    ];

    /**
     * Constructor.
     *
     * @param string     $id
     * @param array|null $options
     */
    public function __construct(string $id, array $options = null)
    {
        parent::__construct($id, AgentInterface::FILE, $options);

        if ($options) {
            // Filter self options only.
            $options = array_filter($options,
                fn($key) => array_key_exists($key, $this->options), 2);

            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        $directory = trim($this->options['directory'] ?? '');
        if ($directory == '') {
            throw new AgentException('Cache directory option cannot be empty');
        }

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new AgentException('Cannot make directory [error: %s]', '@error');
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
            $this->deleteFile($file);
            return false;
        }

        // Check corruption, level=6 (https://stackoverflow.com/a/9050274/362780).
        if ($this->options['compress']
            && $this->options['compressCheck']
            && file_get_contents($file, length: 2) != "\x78\x9C") {
            $this->deleteFile($file);
            return false;
        }

        $time = filemtime($file);
        if (!$time) {
            $this->deleteFile($file);
            return false;
        }

        // Live.
        if ($time > time() - ($ttl ?? $this->ttl)) {
            return true;
        }

        // Dead (do gc).
        $this->deleteFile($file);

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

        $file = $this->file;

        if ($this->options['serialize']) {
            $value = $this->serialize($value);
        }

        if ($this->options['compress']) {
            $value = gzcompress($value, level: 6);
            if ($value === false) {
                $this->deleteFile($file);
                return false;
            }
        }

        return (bool) file_set_contents($file, $value);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $default = null, int $ttl = null)
    {
        if (!$this->has($key, $ttl)) {
            return $default;
        }

        $file = $this->file;

        $value = file_get_contents($file);
        if ($value === false) {
            $this->deleteFile($file);
            return null;
        }

        if ($this->options['compress']) {
            $value = gzuncompress($value);
            if ($value === false) {
                $this->deleteFile($file);
                return null;
            }
        }

        if ($this->options['serialize']) {
            $value = $this->unserialize($value);
        }

        return $value;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return $this->deleteFile($this->prepareFile($key));
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function clear(string $subDirectory = ''): bool
    {
        $directory = $this->options['directory'];
        if ($subDirectory != '') {
            $directory .= '/' . trim($subDirectory, '/');
        }

        $extension = '.cache';

        try {
            // Try fastest way, so far..
            exec('find ' . escapeshellarg($directory)
               . ' -name *' . escapeshellarg($extension)
               . ' -print0 | xargs -0 rm');
        } catch (Error) {
            // Oh my..
            static $rmrf;
            $rmrf ??= function ($directory) use (&$rmrf, $extension) {
                $glob = glob($directory . '/*');
                foreach ($glob as $path) {
                    if (is_dir($path)) {
                        $rmrf($path . '/*');
                        rmdir($path);
                    } elseif (is_file($path) && str_ends_with($path, $extension)) {
                        unlink($path);
                    }
                }
            };

            $rmrf($directory);
        }

        $glob = glob($directory . '/*');

        return empty($glob);
    }

    /**
     * Get options property.
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Set directory option.
     *
     * @return self
     * @since  4.0
     */
    public function setDirectory(string $directory): self
    {
        $this->options['directory'] = $directory;

        return $this;
    }

    /**
     * Get directory option.
     *
     * @return string
     * @since  4.0
     */
    public function getDirectory(): string
    {
        return $this->options['directory'];
    }

    /**
     * Get (prepared) file property.
     *
     * @return string
     * @since  6.0
     * @throws froq\cache\agent\AgentException
     */
    public function file(): string
    {
        return $this->file ?? throw new AgentException(
            'No file yet, try after calling set(), get() or has()'
        );
    }

    /**
     * Prepare file path & set/update file property.
     */
    private function prepareFile(string $key): string
    {
        return $this->file = sprintf(
            '%s/%s.cache', $this->options['directory'], md5($key)
        );
    }

    /**
     * Delete file.
     */
    private function deleteFile(string $file): bool
    {
        return is_file($file) && unlink($file);
    }

    /**
     * Serialize.
     */
    private function serialize(mixed $value): string
    {
        return match ($this->options['serialize']) {
            'php'   => serialize($value),
            'json'  => json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRESERVE_ZERO_FRACTION),
            default => throw new AgentException('Invalid serialize option `%s` [valids: php, json]',
                $this->options['serialize'])
        };
    }

    /**
     * Unserialize.
     */
    private function unserialize(string $value): mixed
    {
        return match ($this->options['serialize']) {
            'php'   => unserialize($value),
            'json'  => json_decode($value),
            default => throw new AgentException('Invalid serialize option `%s` [valids: php, json]',
                $this->options['serialize'])
        };
    }
}
