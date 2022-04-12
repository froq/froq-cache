<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
declare(strict_types=1);

namespace froq\cache\agent;

use Memcached, Redis;

/**
 * A trait, provides native client properties and used by Memcached & Redis agents only.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AgentTrait
 * @author  Kerem Güneş
 * @since   1.0, 5.0
 * @internal
 */
trait AgentTrait
{
    /** @var Memcached|Redis */
    public readonly Memcached|Redis $client;

    /** @var string */
    public readonly string $host;

    /** @var int */
    public readonly int $port;
}
