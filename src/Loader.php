<?php

namespace Sober\Controller;

class Loader
{
    protected $path;
    protected $files;
    protected $instance;
    protected $instances = [];

    public function __construct()
    {
        $this->setPath();

        if (!file_exists($this->path)) return;

        $this->addControllerClasses();
        $this->getFiles();
        $this->load();
    }

    /**
     * Set Path
     *
     * Set the path for the controller files
     */
    protected function setPath()
    {
        $this->path = (has_filter('sober/controller/path') ?  apply_filters('sober/controller/path', rtrim($this->path)) : get_stylesheet_directory() . '/controllers');
    }

    /**
     * Get Files
     *
     * Set the list of files
     */
    protected function getFiles()
    {
        $this->files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
    }

    /**
     * Add Controller Class
     *
     * Add the required body classes
     * @return string
     */
    protected function addControllerClasses()
    {
        add_filter('body_class', function ($body_classes) {
            global $template;
            global $post;

            $classes[] = 'base-controller';
            if (is_singular($post)) $classes[] = 'singular-controller';
            $classes[] = str_replace('.blade.php', '-controller', basename($template));
 
            return array_merge($body_classes, $classes);
        });
    }

    /**
     * Is File Extension
     *
     * Check if the file extension is PHP
     * @return boolean
     */
    protected function isFileExtension($extensions)
    {
        $path = pathinfo($this->instance, PATHINFO_EXTENSION);
        return (in_array($path, $extensions));
    }

    /**
     * Get Instance Class
     *
     * Return the class of the instance
     * @return string
     */
    protected function getInstanceClass()
    {   
        $class = get_declared_classes();
        $class = '\\' . end($class);
        return $class;
    }

    /**
     * Get Instance Template
     *
     * Return the template of the instance
     * @return string
     */
    protected function getInstanceTemplate()
    {
        $class = $this->getInstanceClass();
        $result = (new $class())->template; // returns array
        $path = pathinfo($this->instance->getFileName(), PATHINFO_FILENAME);
        return ($result[0] ? $result : array($path));
    }

    /**
     * Set Instance
     *
     * Add instance name and class to $instances[]
     */
    protected function setInstance()
    {
        $this->instances[] = ['template' => $this->getInstanceTemplate(), 'class' => $this->getInstanceClass()];
    }

    /**
     * Load
     *
     * Load each controller class instance
     */
    protected function load()
    {
        foreach ($this->files as $filename => $file) {
            $this->instance = $file;

            if (!$this->isFileExtension(['php'])) continue;

            include_once $filename;
            $this->setInstance();
        }
    }

    /**
     * Get
     *
     * Return instances from the controller files
     * @return array
     */
    public function get()
    {
        return $this->instances;
    }
}
