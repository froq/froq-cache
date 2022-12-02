<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache;

/**
 * @package froq\cache
 * @class   froq\cache\CacheException
 * @author  Kerem Güneş
 * @since   1.0
 */
class CacheException extends \froq\common\Exception
{
    public static function forEmptyAgentOptions(): static
    {
        return new static('No agent options given');
    }

    public static function forEmptyAgentIdOption(): static
    {
        return new static('No agent id given in options');
    }

    public static function forMissingValueArgument(): static
    {
        return new static('Argument $value is not given');
    }
}
