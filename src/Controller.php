<?php

namespace Sober\Controller;

class Controller
{
    protected $data = [];
    protected $methods;
    protected $exclude;

    public $active = true;
    public $template = false;

    public function __construct()
    {
        $this->setMethods()->setControllerMethods()->tasks();
    }

    /**
     * Set Class methods
     *
     * @return object
     */
    protected function setMethods()
    {
        $class = new \ReflectionClass($this);
        $this->methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        return $this;
    }

    /**
     * Set Class Controller methods
     *
     * @return object
     */
    protected function setControllerMethods()
    {
        $this->exclude = get_class_methods(__CLASS__);
        return $this;
    }

    /**
     * Exclude Class Controller methods
     *
     * @return boolean
     */
    protected function isControllerMethod($method)
    {
        if (in_array($method->name, $this->exclude)) {
            return true;
        }
    }

    /**
     * Tasks
     *
     * Run each of the extended class public methods
     */
    protected function tasks()
    {
        foreach ($this->methods as $method) {
            if ($this->isControllerMethod($method)) {
                continue;
            }
            $this->data[$method->name] = $this->{$method->name}();
        }
    }

    /**
     * Set Class methods
     *
     * @return object
     */
    public function controller()
    {
        return ($this->active ? $this->data : array());
    }
}
