<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-cache
 */
namespace froq\cache\agent;

/**
 * An APCu extension wrapper class.
 *
 * @package froq\cache\agent
 * @class   froq\cache\agent\Apcu
 * @author  Kerem Güneş
 * @since   1.0
 */
class Apcu extends AbstractAgent implements AgentInterface
{
    /**
     * Constructor.
     * @param  string     $id
     * @param  array|null $options
     * @throws froq\cache\agent\AgentException
     */
    public function __construct(string $id = '', array $options = null)
    {
        if (!extension_loaded('apcu')) {
            throw AgentException::forNotFoundExtension('apcu');
        }

        parent::__construct($id, 'apcu', $options);
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
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        return apcu_store($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * @inheritDoc froq\cache\agent\AgentInterface
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $default;

        $value = apcu_fetch($key, $ok);
        if (!$ok) {
            $value = $default;
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
    public function clear(string $prefix = null): bool
    {
        if ($prefix) {
            $result = false;

            foreach (new \APCuIterator('~^' . $prefix . '~') as $item) {
                $result = apcu_delete($item['key']);
            }

            // Bool cast, so key can be an array.
            return (bool) $result;
        }

        return apcu_clear_cache();
    }
}
