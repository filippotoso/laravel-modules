<?php

namespace FilippoToso\LaravelModules\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \FilippoToso\LaravelModules\Support\Module addView($slot, $view, $priority = self::DEFAULT_VIEW_PRIORITY)
 * @method static \FilippoToso\LaravelModules\Support\Module removeView($slot, $view, $priority = self::DEFAULT_ACTION_PRIORITY)
 * @method static array views($slot, $priority = null)
 * @method static loopViews($slot, $definedVars, $closure, $priority = null)
 * @method static \FilippoToso\LaravelModules\Support\Module addAction($name, $callback, $priority = self::DEFAULT_ACTION_PRIORITY)
 * @method static \FilippoToso\LaravelModules\Support\Module removeAction($name, $callback, $priority = self::DEFAULT_ACTION_PRIORITY)
 * @method static doAction($name, ...$args)
 * @method static \FilippoToso\LaravelModules\Support\Module addFilter($name, $callback, $priority = self::DEFAULT_FILTER_PRIORITY)
 * @method static \FilippoToso\LaravelModules\Support\Module removeFilter($name, $callback, $priority = self::DEFAULT_FILTER_PRIORITY)
 * @method static mixed applyFilter($name, $value, ...$args)
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
