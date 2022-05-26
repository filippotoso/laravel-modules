<?php

namespace FilippoToso\LaravelModules\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 
 * @method static array views($slot, $priority = null)
 * @method static \FilippoToso\LaravelModules\Support\Module addView($slot, $view, $priority = self::DEFAULT_PRIORITY)
 * @method static \FilippoToso\LaravelModules\Support\Module removeView($slot, $view, $priority = null)
 * @method static array loopViews($slot, $definedVars, $closure, $priority = null)
 *
 * @see \FilippoToso\LaravelModules\Support\Module
 */

class Module extends Facade
{
    protected static function getFacadeAccessor()
    {
        return static::class;
    }
}
