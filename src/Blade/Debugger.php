<?php

namespace Sober\Controller\Blade;

use Sober\Controller\Blade;

class Debugger extends Blade
{
    private $controller;
    private $controllerDataLog = [];

    /**
     * Construct
     *
     * Initialise the Debugger methods
     */
    public function __construct($data)
    {
        // Set data from $data['__data']['__blade']
        $this->setBladeData($data);

        // Render to view
        $this->render();
    }

    /**
     * Render
     *
     * Loop through $this->data and echo debugger information
     */
    private function render()
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

        echo '<pre class="debugger"><strong>@debug</strong><br>';

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

            // Render data
            if ($controller->data) {
                $this->renderData();
            }

            // Render methods
            if ($controller->methods) {
                $this->renderMethods();
            }

            echo '</ul>';
        }

        echo '</pre>';
    }

    /**
     * Render Data
     *
     * Render the current Controller item data
     */
    private function renderData()
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
            echo "<li>{$name}";

            // Override
            if ($override) {
                echo "<small>overrides {$override}</small>";
            }

            // Data type
            echo "<small>{$dataType}</small>";

            // Method lines
            if (isset($data->method)) {
                echo "<small>line {$data->method->getStartLine()}&mdash;{$data->method->getEndLine()}</small>";
            }

            echo '</li>';
        }

        echo '</ul></li>';
    }

    /**
     * Render Methods
     *
     * Render the current Controller item methods
     */
    private function renderMethods()
    {
        echo '<li>Methods<ul>';

        foreach ($this->controller->methods as $method) {
            echo "<li>{$method->name}<small>line {$method->getStartLine()}&mdash;{$method->getEndLine()}</small></li>";
        }

        echo '</ul></li>';
    }
}
