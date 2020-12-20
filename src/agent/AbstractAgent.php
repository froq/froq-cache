<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

use froq\cache\agent\AgentInterface;

/**
 * Abstract Agent.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\AbstractAgent
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
abstract class AbstractAgent
{
    /** @const int */
    public const TTL = 60; // 1 min.

    /** @var string @since 4.3 */
    protected string $id;

    /** @var string */
    protected string $name;

    /** @var bool @since 4.3 */
    protected bool $static;

    /** @var int */
    protected int $ttl;

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
        $this->static = $options['static'] ?? false;     // @default
        $this->ttl    = $options['ttl']    ?? self::TTL; // @default
    }

    /**
     * Get id property.
     *
     * @return string
     * @since  4.3
     */
    public final function id(): string
    {
        return $this->id;
    }

    /**
     * Get name property.
     *
     * @return string
     * @since  4.3
     */
    public final function name(): string
    {
        return $this->name;
    }

    /**
     * Get static property.
     *
     * @return string
     * @since  4.3
     */
    public final function static(): bool
    {
        return $this->static;
    }

    /**
     * Get/set ttl property.
     *
     * @param  int|null $ttl
     * @return int
     * @since  4.3
     */
    public final function ttl(int $ttl = null): int
    {
        if ($ttl !== null) {
            $this->ttl = $ttl;
        }

        return $this->ttl;
    }

    /**
     * Set ttl.
     *
     * @param  int $ttl
     * @return void
     */
    public final function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * Get ttl.
     *
     * @return int
     */
    public final function getTtl(): int
    {
        return $this->ttl;
    }
}
