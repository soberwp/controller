<?php

namespace Sober\Controller\Blade;

use Sober\Controller\Blade;
use Sober\Controller\Utils;

class Coder extends Blade
{
    private $code = '';
    private $indentation = '';
    private $includes;
    private $codeif;

    /**
     * Construct
     *
     * Initialise the Code methods
     */
    public function __construct($data, $includes, $codeif = false)
    {
        // codeif
        $this->codeif = $codeif;

        // Set data from @code('var')
        $this->setIncludeData($includes);

        // Set data from $data['__data']['__blade']
        $this->setBladeData($data);

        // Render to view
        $this->render();
    }

    private function setIncludeData($includes)
    {
        $this->includes = $includes;

        if (is_string($this->includes)) {
            $this->includes = [$this->includes];
        }
    }

    /**
     * Increase Indentation
     *
     * Add two spaces to $this->indentation
     */
    private function increaseIndentation()
    {
        $this->indentation = "{$this->indentation}  ";
    }

    /**
     * Decrease Indentation
     *
     * Remove two spaces from $this->indentation
     */
    private function decreaseIndentation()
    {
        $this->indentation = substr($this->indentation, 0, -2);
    }

    /**
     * Render
     *
     * Loop through $this->data and echo code
     */
    private function render()
    {
        // Map the data results to exclude static methods
        $this->data = array_map(function ($item) {
            return $item->data;
        }, $this->data);

        // Remove the first level of the array so that we are left with flat variables
        $this->data = call_user_func_array('array_merge', $this->data);

        // Remove $post by default
        unset($this->data['post']);

        // Start @code block
        $type = ($this->codeif ? '@codeif' : '@code');
        echo "<pre class=\"coder\"><strong>{$type}</strong><br>";

        // Run through each item
        foreach ($this->data as $name => $value) {
            // Remove the method/returned for data methods
            $value = (isset($value->method) ? $value->returned : $value);
            
            // Router
            // @code('var')
            if ($this->includes && in_array($name, $this->includes)) {
                $this->router($name, $value);
            }

            // @code
            if (!$this->includes) {
                $this->router($name, $value);
            }
        }

        // End @code block
        echo '</pre>';
    }

    /**
     * Router
     *
     * Route data types to correct methods
     */
    private function router($name, $val)
    {
        // Route object
        if (is_object($val)) {
            $this->renderObj($name, $val);
        }

        // Route indexed array
        if (is_array($val) && Utils::isArrayIndexed($val)) {
            $this->renderArrIndexed($name, $val);
        }

        // Route array with keys
        if (is_array($val) && !Utils::isArrayIndexed($val)) {
            $this->renderArrKeys($name, $val);
        }

        // Route strings/other
        if (!is_array($val) && !is_object($val)) {
            // Add to $this->code
            $this->renderResult($name, $val);

            // Clear out $this-code
            $this->code = '';

            // Exit
            return;
        }
    }

    /**
     * Render Object
     *
     * Render an object
     */
    private function renderObj($name, $val)
    {
        // Get props of object
        $props = get_object_vars($val);

        // For each of those props
        foreach ($props as $prop_name => $prop_val) {
            // Add to $this->code
            $this->code = "{$this->code}{$name}->";

            // Route new values
            $this->router($prop_name, $prop_val);
        }
    }

    /**
     * Render Indexed Array
     *
     * Render an indexed array
     */
    private function renderArrIndexed($name, $val)
    {
        if ($this->codeif) {
            // Echo if
            echo "{$this->indentation}@if (\${$this->code}$name)<br>";

            // Increase indentation
            $this->increaseIndentation();
        }

        // Start foreach
        echo "{$this->indentation}@foreach (\${$this->code}$name as \$item)<br>";

        // Clear $this->code
        $this->code = '';

        // Increase indentation
        $this->increaseIndentation();

        // Route next value
        foreach ($val as $key_index => $key_val) {
            if (count($val) > 1) {
                echo "{$this->indentation}<strong>[{$key_index}]</strong><br>";
            }
            $this->router('item', $key_val);
        }

        // Decrease indentation
        $this->decreaseIndentation();
    
        // End foreach
        echo "{$this->indentation}@endforeach<br>";

        if ($this->codeif) {
            // Decrease indentation
            $this->decreaseIndentation();

            // Echo endif
            echo "{$this->indentation}@endif<br>";
        }
    }

    /**
     * Render Array Keys
     *
     * Render an array with keys
     */
    private function renderArrKeys($name, $val)
    {
        // Foreach value add key
        foreach ($val as $key_name => $key_val) {
            $this->router("{$this->code}{$name}['{$key_name}']", $key_val);
        }
    }

    /**
     * Render Result
     *
     * Render the final result
     */
    private function renderResult($name, $val)
    {
        $this->code = "\${$this->code}{$name}";

        if ($this->codeif) {
            // Echo if
            echo "{$this->indentation}@if ({$this->code})<br>";

            // Increase indentation
            $this->increaseIndentation();
        }

        // Wrap with {{ }} or {!! !!}
        if (Utils::doesStringContainMarkup($val)) {
            $this->code = "{!! {$this->code} !!}";
        } else {
            $this->code = "{{ {$this->code} }}";
        }

        // Echo code
        echo "{$this->indentation}{$this->code}<br>";
        
        if ($this->codeif) {
            // Increase indentation
            $this->decreaseIndentation();

            // Echo endif
            echo "{$this->indentation}@endif<br>";
        }
    }
}
