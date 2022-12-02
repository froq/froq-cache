<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * @package froq\cache\agent
 * @class   froq\cache\agent\AgentException
 * @author  Kerem Güneş
 * @since   4.0
 */
class AgentException extends \froq\cache\CacheException
{
    /**
     * Create for not found extension.
     */
    public static function forNotFoundExtension(string $name): static
    {
        return new static('Extension %q not found', $name);
    }

    /**
     * Create for empty host or port.
     */
    public static function forEmptyHostOrPort(): static
    {
        return new static('Host or port cannot be empty');
    }

    /**
     * Create for empty directory option.
     */
    public static function forEmptyDirectoryOption(): static
    {
        return new static('Option "directory" cannot be empty');
    }

    /**
     * Create for make directory error.
     */
    public static function forMakeDirectoryError(string $directory): static
    {
        return new static('Cannot create cache directory %S [error: @error]', $directory);
    }

    /**
     * Create for invalid serialize option.
     */
    public static function forInvalidSerializeOption(mixed $option): static
    {
        return new static('Invalid serialize option %q [valids: php, json]', $option);
    }
}
