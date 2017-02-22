<?php

namespace Sober\Controller\Module;

class Debugger
{
    protected $data;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;

        $this->sanitize();
        $this->route();
    }

    /**
     * Sanitize
     *
     * Remove __env, app and obLevel arrays from data
     */
    protected function sanitize()
    {
        $this->data = array_diff_key($this->data['__data'], array_flip(['__env', 'app', 'obLevel']));
    }

    /**
     * Route
     *
     * Run method depending on type
     */
    public function route()
    {
        if ($this->type === 'dump') $this->dump();
        if ($this->type === 'config') $this->config();
        if ($this->type === 'controller') $this->controller();
    }

    /**
     * Dump
     *
     * Return var_dump of data
     */
    public function dump()
    {
        var_dump($this->data);
    }

    /**
     * Config
     *
     * Return list of keys and values from data
     */
    public function config()
    {
        var_dump($this->data['_config']);
    }

    /**
     * Controller
     *
     * Return list of keys from data
     */
    public function controller()
    {
        unset($this->data['post']);
        echo '<pre><strong>Controller Debugger:</strong><ul>';
        foreach ($this->data as $name => $item) {
            $item = (is_array($item) ? gettype($item) . '[' . count($item) . ']' : gettype($item));
            echo "<li>$" . $name . " &raquo; " . $item . "</li>";
        }
        echo '</ul></pre>';
    }
}
