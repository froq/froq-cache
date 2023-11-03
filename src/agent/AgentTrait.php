<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * @package froq\cache\agent
 * @class   froq\cache\agent\AgentTrait
 * @author  Kerem Güneş
 * @since   1.0, 5.0
 */
trait AgentTrait
{
    /** Client instance. */
    public readonly \Memcached|\Redis $client;

    public readonly string $host;
    public readonly int $port;
}
