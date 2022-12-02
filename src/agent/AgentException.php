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
    public static function forNotFoundExtension(string $name): static
    {
        return new static('Extension %q not found', $name);
    }

    public static function forEmptyHostOrPort(): static
    {
        return new static('Host or port cannot be empty');
    }

    public static function forEmptyDirectoryOption(): static
    {
        return new static('Option "directory" cannot be empty');
    }

    public static function forMakeDirectoryError(string $directory): static
    {
        return new static('Cannot create cache directory %S [error: @error]', $directory);
    }

    public static function forNoFilePreparedYet(): static
    {
        return new static('No file prepared yet, try after calling set(), get() or has()');
    }

    public static function forInvalidArgumentValue(mixed $value): static
    {
        return new static(
            'Argument $value must be string, %t given [tip: use "serialize" option for serialization]',
            $value
        );
    }

    public static function forInvalidSerializeOption(mixed $option): static
    {
        return new static('Invalid serialize option %q [valids: php, json]', $option);
    }
}
