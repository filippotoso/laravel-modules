<?php

namespace FilippoToso\LaravelModules\Support\Traits;

trait HasCallback
{
    /**
     * The action callback
     */
    protected $callback;

    /**
     * UUID of the action
     *
     * @var string
     */
    protected $uuid;

    /**
     * Constructor
     *
     * @param mixed $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
        $this->uuid = static::uuid($this->callback);
    }

    /**
     * Static constructor
     *
     * @param mixed $callback
     * @return self
     */
    public static function make($callback)
    {
        return new static($callback);
    }

    public static function uuid($callback)
    {
        if (is_string($callback)) {
            return $callback;
        }

        if (is_object($callback)) {
            return spl_object_hash($callback);
        }

        if (is_array($callback)) {
            return ($callback[0] ?? null) . '::' . ($callback[1] ?? null);
        }

        return null;
    }

    public function __get($name)
    {
        $attributes = ['callback', 'uuid'];

        if (in_array($name, $attributes)) {
            return $this->$name;
        }

        trigger_error(
            sprintf('Undefined property via __get(): %s in %s on line %s.', $name, __FILE__, __LINE__),
            E_USER_NOTICE
        );

        return null;
    }
}
