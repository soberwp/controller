<?php

namespace Sober\Controller;

class Loader
{
    protected $path;
    protected $file;
    protected $files;
    protected $instance;
    protected $instances = [];

    public function __construct()
    {
        $this->setPath();

        if (!file_exists($this->path)) return;

        $this->getFiles();
        $this->load();
        $this->addBodyClass();
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
     * Add Body Class
     *
     * Add the required global body class
     * @return string
     */
    protected function addBodyClass()
    {
        add_filter('body_class', function ($classes) {
            return array_merge($classes, ['global']);
        });
    }

    /**
     * Get Instance Class
     *
     * Return the class of the instance
     * @return string
     */
    protected function getInstanceClass()
    {
        return '\\' . end(get_declared_classes());
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
        $result = (new $class())->template;
        return ($result ? $result : array(pathinfo($this->instance->getFileName(), PATHINFO_FILENAME)));
    }

    /**
     * Is Instance
     *
     * Check if the file extension is PHP
     * @return boolean
     */
    protected function isInstance()
    {
        return (in_array(pathinfo($this->instance, PATHINFO_EXTENSION), ['php']));
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
            if (!$this->isInstance()) continue;
            include_once $filename;
            $this->setInstance();
        }
    }

    /**
     * Controllers
     *
     * Return instances from the controller files
     * @return array
     */
    public function controllers()
    {
        return $this->instances;
    }
}
