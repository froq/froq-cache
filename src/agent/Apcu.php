<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\cache\agent;

use froq\cache\agent\{AbstractAgent, AgentInterface, AgentException};

/**
 * Apcu.
 *
 * @package froq\cache\agent
 * @object  froq\cache\agent\Apcu
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Apcu extends AbstractAgent implements AgentInterface
{
    /**
     * Constructor.
     * @param  string     $id
     * @param  array|null $options
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $id, array $options = null)
    {
        extension_loaded('apcu') || throw new AgentException('APCu extension not found');

        parent::__construct($id, AgentInterface::APCU, $options);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function init(): AgentInterface
    {
        return $this;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function has(string $key): bool
    {
        return apcu_exists($key);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        return apcu_store($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, $default = null)
    {
        $value = $default;

        if (apcu_exists($key)) {
            $value = apcu_fetch($key, $ok);
            if (!$ok) {
                $value = $default;
            }
        }

        return $value;
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function delete(string $key): bool
    {
        return apcu_delete($key);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function clear(): bool
    {
        return apcu_clear_cache('user');
    }
}
