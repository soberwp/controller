<?php

namespace Sober\Controller;

class Debugger
{
    protected $data;

    public function __construct($data, $dump = false)
    {
        $this->data = $data;

        $this->sanitize();
    }

    /**
     * Sanitize
     *
     * Remove __env, app and obLevel arrays from data
     */
    protected function sanitize()
    {
        $this->data = array_diff_key($this->data['__data'], array_flip(array('__env', 'app', 'obLevel')));
    }

    /**
     * Debug
     *
     * Return var_dump of data
     */
    public function debug()
    {
        var_dump($this->data);
    }

    /**
     * Controller
     *
     * Return list of keys from data
     */
    public function controller()
    {
        unset($this->data['post']);
        echo '<pre><strong>Controller Debugger</strong><ul>';
        foreach ($this->data as $name => $item) {
            $item = (is_array($item) ? gettype($item) . '[' . count($item) . ']' : gettype($item));
            echo "<li>$" . $name . " &raquo; " . $item . "</li>";
        }
        echo '</ul></pre>';
    }
}
