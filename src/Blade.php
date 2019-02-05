<?php

namespace Sober\Controller;

class Blade
{
    protected $data;

    /**
     * Set Data
     *
     * Remove other array items should last item not include tree
     */
    protected function setBladeData($data)
    {
        // Get __blade/__debugger key
        $this->data = $data['__data']['__blade'];

        // Only worry about rewriting data if we have more than one class
        if (count($this->data) > 1) {
            // Get first item from data array
            $first = reset($this->data);

            // Get last item from data array
            $last = end($this->data);

            // If last item does not inherit tree and first class is App
            if (!$last->tree && $first->class === 'App') {
                // Rewrite $this->data with first (App) and last item in array
                $this->data = [$first, $last];
            // Else if $last does not inherit tree
            } elseif (!$last->tree) {
                // Rewrite $this->data with last item in array
                $this->data = $last;
            }
        }

        return $this;
    }
}
