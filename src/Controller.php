<?php

namespace Sober\Controller;

class Controller
{
    protected $active = true;
    protected $tree = false;
    protected $data = [];

    private $class;
    private $methods;

    public function __setup()
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
        $excls = get_class_methods(__CLASS__);
        $excls[] = '__construct';
        return (in_array($method->name, $excls));
    }

    /**
     * Is Static Method
     *
     * Return true if the method is static
     * @return boolean
     */
    private function __isStaticMethod($method)
    {
        $excls = [];
        $statics = $this->class->getMethods(\ReflectionMethod::IS_STATIC);
        foreach ($statics as $static) {
            $excls[] = $static->name;
        }
        return (in_array($method->name, $excls));
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
     * Run and convert each of the child class public methods
     */
    private function __runMethods()
    {
        foreach ($this->methods as $method) {
            if ($this->__isControllerMethod($method) || $this->__isStaticMethod($method)) {
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
