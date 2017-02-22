<?php

namespace Sober\Controller\Loader;

use Sober\Controller\Loader;

class Controller extends Loader
{
    public function run()
    {
        $this->load();
        $this->addBaseClass();
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
     * Add Base Class
     *
     * Add the required global body class
     * @return string
     */
    protected function addBaseClass()
    {
        add_filter('body_class', function ($classes) {
            return array_merge($classes, ['base']);
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
        $path = pathinfo($this->instance->getFileName(), PATHINFO_FILENAME);
        return ($result ? $result : array($path));
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
