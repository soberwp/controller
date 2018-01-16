<?php

namespace Sober\Controller;

use Sober\Controller\Utils;

class Debugger
{
    private $data;
    private $controller;
    private $controllerDataLog = [];

    /**
     * Construct
     *
     * Initialise the Debugger methods
     */
    public function __construct($data)
    {
        $this->data = $data['__data']['__debugger'];

        $this->setData();
        $this->display();
    }

    /**
     * Set Data
     *
     * Remove other array items should last item not include tree
     */
    private function setData()
    {
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

    /**
     * Display
     *
     * Loop through $this->data and echo debugger information
     */
    private function display()
    {
        echo '
            <style>
            .debugger small {
                border: 1px solid rgba(0,0,0,0.2); 
                opacity: 0.5; 
                padding: 2px 5px; 
                margin-left: 5px; 
                border-radius: 2px;
            }
            </style>';

        echo '<pre class="debugger"><strong>Debugger:</strong>';
        echo '<br><br>';

        foreach ($this->data as $index => $controller) {
            // Set the class params for each Controller
            $this->controller = $controller;

            // Echo the Controller class name
            echo $controller->class;

            // Echo extends tree if the Controller implements tree
            if ($controller->tree) {
                echo '<small>extends tree</small>';
            }

            echo '<ul>';

            // Display Controller data
            if ($controller->data) {
                $this->displayControllerData();
            }

            // Display Controller methods
            if ($controller->methods) {
                $this->displayControllerMethods();
            }

            echo '</ul>';
        }
        echo '</pre>';
    }

    /**
     * Display Controller Data
     *
     * Display the current Controller item data
     */
    private function displayControllerData()
    {
        echo '<li>Data<ul>';

        foreach ($this->controller->data as $name => $data) {
            // Remove previous overrides
            $override = false;

            // Search the data log array for this name
            $key = array_search($name, array_column($this->controllerDataLog, 'name'));

            // If the name exists in the data log array then get the class
            if ($key !== false) {
                $override = $this->controllerDataLog[$key]['class'];
            }

            // Update data log array with this items name and class
            $this->controllerDataLog[] = [
                'name' => $name,
                'class' => $this->controller->class
            ];

            // Get data type
            $dataType = (isset($data->method) ? gettype($data->returned) : gettype($data));

            // Echo
            echo '<li>';
            echo $name;

            // Override
            if ($override) {
                echo '<small>overrides ' . $override . '</small>';
            }

            // Data type
            echo '<small>' . $dataType . '</small>';

            // Method lines
            if (isset($data->method)) {
                echo '<small>line ' . $data->method->getStartLine() . '&mdash;' . $data->method->getEndLine() . '</small>';
            }

            echo '</li>';
        }

        echo '</ul></li>';
    }

    /**
     * Display Controller Methods
     *
     * Display the current Controller item methods
     */
    private function displayControllerMethods()
    {
        echo '<li>Methods<ul>';

        foreach ($this->controller->methods as $method) {
            echo '<li>';
            echo  $method->name;
            echo '<small>' . $method->getStartLine() . '&mdash;' . $method->getEndLine() . '</small>';
            echo '</li>';
        }

        echo '</ul></li>';
    }
}
