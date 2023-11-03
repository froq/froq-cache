<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * An abstract class that extended by agent classes.
 *
 * @package froq\cache\agent
 * @class   froq\cache\agent\AbstractAgent
 * @author  Kerem Güneş
 * @since   1.0
 */
abstract class AbstractAgent
{
    /** Default TTL value. */
    public final const TTL = 60; // 1 min.

    public readonly string $id;
    public readonly string $name;
    public readonly bool $static;
    public readonly int $ttl;

    /**
     * Constructor.
     *
     * @param string     $id
     * @param string     $name
     * @param array|null $options
     */
    public function __construct(string $id, string $name, array $options = null)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->static = (bool) ($options['static'] ?? false); // @default
        $this->ttl    = (int) ($options['ttl'] ?? self::TTL); // @default
    }
}
