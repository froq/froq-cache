<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * A file cache wrapper class.
 *
 * @package froq\cache\agent
 * @class   froq\cache\agent\File
 * @author  Kerem Güneş
 * @since   1.0
 */
class File extends AbstractAgent implements AgentInterface
{
    /** Prepared cache file at runtime. */
    private string $file;

    /** Options. */
    private array $options = [
        'directory'     => null,  // Must be given in constructor.
        'serialize'     => 'php', // Only 'php' or 'json'.
        'compress'      => false, // Compress data.
        'compressCheck' => false, // Verify compressed data.
    ];

    /**
     * Constructor.
     *
     * @param string     $id
     * @param array|null $options
     */
    public function __construct(string $id = '', array $options = null)
    {
        parent::__construct($id, 'file', $options);

        if ($options) {
            // Filter self options only.
            $options = array_filter_keys($options, fn(int|string $key): bool => (
                array_key_exists($key, $this->options)
            ));

            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        $directory = (string) $this->options['directory'];

        if (trim($directory) === '') {
            throw AgentException::forEmptyDirectoryOption();
        }

        if (!@dirmake($directory)) {
            throw AgentException::forMakeDirectoryError($directory);
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
            && file_get_contents($file, length: 2) !== "\x78\x9C") {
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
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        if ($this->has($key, $ttl)) {
            return true;
        }

        $file = $this->file;

        if ($this->options['serialize']) {
            $value = $this->serialize($value);
        } else {
            is_string($value) || throw new AgentException(
                'Argument $value must be string, %t given '.
                '[tip: use "serialize" option for serialization]',
                $value
            );
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
    public function get(string $key, mixed $default = null, int $ttl = null): mixed
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
        if ($subDirectory !== '') {
            $directory .= '/' . trim($subDirectory, '/');
        }

        $extension = '.cache';

        try {
            // Try fastest way, so far..
            exec(
                'find ' . escapeshellarg($directory) . ' '  .
                '-name *' . escapeshellarg($extension) . ' '  .
                '-print0 | xargs -0 rm 2> /dev/null'
            );
            clearstatcache();
        } catch (\Error) {
            // Oh, my lad..
            $rmrf = function (string $root) use (&$rmrf, $extension): void {
                if ($paths = glob($root . '/*')) {
                    foreach ($paths as $path) {
                        if (is_dir($path)) {
                            $rmrf($path . '/*');
                            rmdir($path);
                        } elseif (is_file($path)) {
                            str_ends_with($path, $extension)
                            && unlink($path);
                        }
                    }
                }
            };

            $rmrf($directory);
        }

        return empty(glob($directory . '/*'));
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
            default => throw AgentException::forInvalidSerializeOption($this->options['serialize'])
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
            default => throw AgentException::forInvalidSerializeOption($this->options['serialize'])
        };
    }
}
