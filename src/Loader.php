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
        $this->setPath()->getFiles();
        $this->load();
    }

    /**
     * Set path
     *
     * Set the path for the controller files
     * @return object
     */
    protected function setPath()
    {
        $this->path = (has_filter('sober/controller/path') ?  apply_filters('sober/controller/path', rtrim($this->path)) : get_stylesheet_directory() . '/controllers');
        return $this;
    }

    /**
     * Get Files
     *
     * Set the list of files
     * @return object
     */
    protected function getFiles()
    {
        if (!file_exists($this->path)) {
            return;
        }
        $this->files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
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
        if (in_array(pathinfo($this->instance, PATHINFO_EXTENSION), ['php'])) {
            return true;
        }
    }

    /**
     * Set instance
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
        if (!file_exists($this->path)) {
            return;
        }
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
