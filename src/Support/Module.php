<?php

namespace FilippoToso\LaravelModules\Support;

class Module
{
    protected const DEFAULT_VIEW_PRIORITY = 10;

    /**
     * Views to be rendered
     *
     * @var array[string]
     */
    protected $views;

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
     * @param int|null $priority If null, search in any priority
     * @return Module
     */
    public function removeView($slot, $view, $priority = null)
    {
        // Search in any priority
        if (is_null($priority)) {
            foreach ($this->views[$slot] as $priority => $views) {
                foreach ($views as $id => $currentView) {
                    if ($currentView == $view) {
                        unset($this->views[$slot][$priority][$id]);
                        return;
                    }
                }
            }
        }

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
     * Loop trough the views of a $slot
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
}
