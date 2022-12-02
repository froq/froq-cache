<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache;

/**
 * @package froq\cache
 * @class   froq\cache\CacheFactoryException
 * @author  Kerem Güneş
 * @since   4.1, 4.3
 */
class CacheFactoryException extends CacheException
{
    public static function forEmptyAgentOption(): static
    {
        return new static('Option "agent" is empty');
    }

    public static function forUnimplementedAgentOption(string $agent): static
    {
        return new static('Unimplemented agent %q', $agent);
    }

    public static function forNoCacheWithId(string $id): static
    {
        return new static(
            'No cache initiated with id %q, call %s::init() to initiate it first',
            [$id, CacheFactory::class]
        );
    }

    public static function forNoCacheAgentWithId(string $id): static
    {
        return new static(
            'No cache agent initiated with id %q as static, call %s::initAgent() '.
            'with static=true option to initiate it first',
            [$id, CacheFactory::class]
        );
    }
}
