<?php

namespace FilippoToso\LaravelModules\Support;

class Module
{
    protected const DEFAULT_VIEW_PRIORITY = 10;
    protected const DEFAULT_ACTION_PRIORITY = 10;
    protected const DEFAULT_FILTER_PRIORITY = 10;

    /**
     * Views to be rendered
     *
     * @var array[string]
     */
    protected $views;

    /**
     * Actions
     *
     * @var array[Action]
     */
    protected $actions;

    /**
     * Filters
     *
     * @var array[Filter]
     */
    protected $filters;

    /**
     * Add a $view to be rendered in a specified $slot
     *
     * @param string $slot
     * @param string $view
     * @param int $priority
     * @return Module
     */
    public function addView($slot, $view, $priority = self::DEFAULT_VIEW_PRIORITY)
    {
        $this->views[$slot][$priority][] = $view;

        ksort($this->views[$slot]);

        return $this;
    }

    /**
     * Removes a $view from a $slot
     *
     * @param string $slot
     * @param string $view
     * @param int $priority 
     * @return Module
     */
    public function removeView($slot, $view, $priority = self::DEFAULT_ACTION_PRIORITY)
    {
        $this->views[$slot][$priority] = $this->views[$slot][$priority] ?? [];
        $this->views[$slot][$priority] = array_diff($this->views[$slot][$priority], [$view]);

        return $this;
    }

    /**
     * Get a list of views for the $slot and $priority (optional)
     *
     * @param string $slot
     * @param int|null $priority If null, search in any priority
     * @return array[string]
     */
    public function views($slot, $priority = null)
    {
        if (is_null($priority)) {

            $results = [];

            foreach ($this->views[$slot] ?? [] as $views) {
                foreach ($views as $view) {
                    $results[] = $view;
                }
            }

            return $results;
        }

        return $this->views[$slot][$priority] ?? [];
    }

    /**
     * Loop trough the views of a $slot (used in Blade @module)
     *
     * @param string $slot
     * @param collable $closure
     * @param array $definedVars The result of get_defined_vars()
     * @param int|null $priority If null, search in any priority
     * @return void
     */
    public function loopViews($slot, $definedVars, $closure, $priority = null)
    {
        $views = $this->views($slot, $priority);

        foreach ($views as $view) {
            $closure($view, $definedVars);
        }
    }

    /**
     * Add an action, returns its uuid
     *
     * @param string $name
     * @param callable $callback
     * @param int $priority
     * @return Module
     */
    public function addAction($name, $callback, $priority = self::DEFAULT_ACTION_PRIORITY)
    {
        $action = Action::make($callback);

        $this->actions[$name][$priority][$action->uuid] = $action;

        ksort($this->actions[$name]);

        return $this;
    }

    /**
     * Remove an action
     *
     * @param string $name
     * @param int|null $priority
     * @return self
     */
    public function removeAction($name, $callback, $priority = self::DEFAULT_ACTION_PRIORITY)
    {
        $uuid = Action::uuid($callback);

        unset($this->actions[$name][$priority][$uuid]);

        return $this;
    }

    /**
     * Execute an action
     *
     * @param string $name
     * @param mixed ...$args
     * @return void
     */
    public function action($name, ...$args)
    {
        foreach ($this->actions[$name] ?? [] as $actions) {
            foreach ($actions as $action) {
                $action->do(...$args);
            }
        }
    }

    /**
     * Add an filter, returns its uuid
     *
     * @param string $name
     * @param callable $callback
     * @param int $priority
     * @return Module
     */
    public function addFilter($name, $callback, $priority = self::DEFAULT_FILTER_PRIORITY)
    {
        $filter = Filter::make($callback);

        $this->filters[$name][$priority][$filter->uuid] = $filter;

        ksort($this->filters[$name]);

        return $this;
    }

    /**
     * Remove filter
     *
     * @param string $name
     * @param int|null $priority 
     * @return self
     */
    public function removeFilter($name, $callback, $priority = self::DEFAULT_FILTER_PRIORITY)
    {
        $uuid = Filter::uuid($callback);

        unset($this->filters[$name][$priority][$uuid]);

        return $this;
    }

    /**
     * Apply a filter
     *
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     */
    public function filter($name, $value, ...$args)
    {
        foreach ($this->filters[$name] ?? [] as $filters) {
            foreach ($filters as $filter) {
                $value = $filter->apply($value, ...$args);
            }
        }

        return $value;
    }
}
