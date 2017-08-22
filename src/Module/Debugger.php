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
        if ($this->type === 'hierarchy') {
            $this->hierarchy();
        }

        if ($this->type === 'dump') {
            $this->dump();
        }

        if ($this->type === 'controller') {
            $this->controller();
        }
    }

    /**
     * Debug Dump
     *
     * Return var_dump of data
     */
    public function dump()
    {
        var_dump($this->data);
    }

    /**
     * Debug Controller
     *
     * Return list of keys from data
     */
    public function controller()
    {
        // unset($this->data['post']);
        echo '<pre><strong>Controller Debugger:</strong><ul>';
        foreach ($this->data as $name => $item) {
            $item = (is_array($item) ? gettype($item) . '[' . count($item) . ']' : gettype($item));
            echo "<li>$" . $name . " &raquo; " . $item . "</li>";
        }
        echo '</ul></pre>';
    }

    /**
     * Debug Hierarchy
     *
     * Return list of hierarchy
     */
    public function hierarchy()
    {
        global $wp_query;
        $templates = (new \Brain\Hierarchy\Hierarchy())->getTemplates($wp_query);
        $templates[] = 'app.php';
        
        $templates = array_map(function ($template) {
            if ($template === 'index') {
                $template = 'index.php';
            }
            if (strpos($template, '.blade.php')) {
                $template = str_replace('.blade', '', $template);
            }
            return basename($template);
        }, $templates);
        
        $templates = array_reverse(array_unique($templates));

        $path = get_stylesheet_directory() . '/controllers';
        $path = (has_filter('sober/controller/path') ? apply_filters('sober/controller/path', rtrim($path)) : dirname(get_template_directory()) . '/app/controllers');
        $path = basename($path);

        echo '<pre><strong>Hierarchy Debugger:</strong><ul>';
        foreach ($templates as $template) {
            echo "<li>" . $path . '/' . $template . "</li>";
        }
        echo '</ul></pre>';
    }
}
