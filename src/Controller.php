<?php

namespace Sober\Controller;

class Controller
{
    private $data = [];
    private $methods;
    private $exclude;

    public $active = true;
    public $template = false;

    public function __construct()
    {
        $this->__setMethods();
        $this->__setControllerMethods();
        $this->__sanitizeTemplate();
        $this->__tasks();
    }

    /**
     * Set Methods
     *
     * Set the Class methods
     * @return object
     */
    private function __setMethods()
    {
        $class = new \ReflectionClass($this);
        $this->methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
    }

    /**
     * Set Controller Methods
     *
     * Set the parent controller methods
     * @return object
     */
    private function __setControllerMethods()
    {
        $this->exclude = get_class_methods(__CLASS__);
    }

    /**
     * Is Controller Method
     *
     * Return true if the method belongs to the parent class
     * @return boolean
     */
    private function __isControllerMethod($method)
    {
        if (in_array($method->name, $this->exclude)) {
            return true;
        }
    }

    /**
     * Sanitize Template
     *
     * Check for all string and add global
     * @return object
     */
    private function __sanitizeTemplate()
    {
        $this->template = (is_array($this->template) ? $this->template : array($this->template));
        if (in_array('all', $this->template)) {
            $this->template[] = 'global';
        }
    }

    /**
     * Sanitize Method
     *
     * Change method name from camel case to snake case
     * @return string
     */
    private function __sanitizeMethod($method)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $method));
    }

    /**
     * Tasks
     *
     * Run each of the extended class public methods
     */
    private function __tasks()
    {
        foreach ($this->methods as $method) {
            if ($this->__isControllerMethod($method)) continue;
            $this->data[$this->__sanitizeMethod($method->name)] = $this->{$method->name}();
        }
    }

    /**
     * Controller
     *
     * Set the class methods to be run
     * @return array
     */
    public function __controller()
    {
        return ($this->active ? $this->data : array());
    }
}
