<?php

namespace FilippoToso\LaravelModules\Support;

use FilippoToso\LaravelModules\Support\Traits\HasCallback;

class Filter
{
    use HasCallback;

    /**
     * Apply the filter
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function apply(...$args)
    {
        return call_user_func_array($this->callback, $args);
    }
}
