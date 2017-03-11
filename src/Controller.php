<?php

namespace Sober\Controller;

class Controller
{
    protected $active = true;
    protected $tree = false;

    private $class;
    private $methods;
    private $data = [];

    public function __construct()
    {
        $this->__setClass();
        $this->__setMethods();
        $this->__runMethods();
    }

    /**
     * Set Class
     *
     * Create a ReflectionClass object for this instance
     */
    private function __setClass()
    {
        $this->class = new \ReflectionClass($this);
    }

    /**
     * Set Methods
     *
     * Set all Class public methods for this instance
     */
    private function __setMethods()
    {
        $this->methods = $this->class->getMethods(\ReflectionMethod::IS_PUBLIC);
    }

    /**
     * Set Tree Data
     *
     * @return array
     */
    public function __setTreeData($data)
    {
        if (!$this->class->implementsInterface('\Sober\Controller\Module\Tree') && $this->tree === false) {
            $data = [];
        }
        return $data;
    }

    /**
     * Is Controller Method
     *
     * Return true if the method belongs to the parent class
     * @return boolean
     */
    private function __isControllerMethod($method)
    {
        return (in_array($method->name, get_class_methods(__CLASS__)));
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
     * Run Methods
     *
     * Run each of the child class public methods
     */
    private function __runMethods()
    {
        foreach ($this->methods as $method) {
            if ($this->__isControllerMethod($method)) {
                continue;
            }
            $this->data[$this->__sanitizeMethod($method->name)] = $this->{$method->name}();
        }
    }

    /**
     * Returns Data
     *
     * Set the class methods to be run
     * @return array
     */
    public function __getData()
    {
        return ($this->active ? $this->data : array());
    }
}
