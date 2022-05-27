<?php

namespace FilippoToso\LaravelModules\Support;

use FilippoToso\LaravelModules\Support\Traits\HasCallback;

class Action
{
    use HasCallback;

    /**
     * Executes the action
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function do(...$args)
    {
        call_user_func_array($this->callback, $args);
    }
}
