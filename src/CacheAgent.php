<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache;

/**
 * Enum class for agents.
 *
 * @package froq\cache
 * @class   froq\cache\CacheAgent
 * @author  Kerem Güneş
 * @since   7.1
 */
class CacheAgent
{
    /** Agents. */
    public const FILE      = 'file',
                 APCU      = 'apcu',
                 REDIS     = 'redis',
                 MEMCACHED = 'memcached';
}
