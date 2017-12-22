<?php

namespace Sober\Controller;

use Illuminate\View\Compilers\BladeCompiler;

abstract class Controller
{
    protected $active = true;
    protected $tree = false;
    protected $data = [];
    protected $directives = [];

    private $class;
    private $methods;

    private static $_instances;

    /**
     * Registered blade directives
     *
     * Used when traversing the template hierarchy to deregister directives from parent controllers
     *
     * @var array
     */
    private static $_activeDirectives = [];

    /**
     * List of blade directives which are not allowed to be used
     *
     * @var array
     */
    private static $_restrictedBladeDirectives = [
        'json',
        'verbatim',
        'endverbatim',
        'php',
        'endphp',
        'include',
        'includeIf',
        'includeWhen',
        'includeFirst',
        'each',
        'push',
        'endpush',
        'stack',

        // Control structures
        'if',
        'elseif',
        'else',
        'endif',
        'unless',
        'endunless',
        'isset',
        'endisset',
        'empty',
        'endempty',
        'switch',
        'case',
        'endswitch',

        // Loops
        'for',
        'endfor',
        'foreach',
        'endforeach',
        'forelse',
        'endforelse',
        'while',
        'endwhile',
    ];

    protected function __construct() {}

    public function __setup()
    {
        if ($this->class) {
            // Do not setup the class again
            return;
        }

        $this->__setClass();
        $this->__setMethods();
        $this->__runMethods();
    }

    final public static function getInstance()
    {
        if(!isset(self::$_instances[static::class])) {
            self::$_instances[static::class] = new static();
        }

        return self::$_instances[static::class];
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
     * Register directives with the blade container
     *
     * When the current controller does not implement the Tree interface we want to reset all previously
     * registered directives from parent controllers except for the base controller (App)
     *
     * @param array $appDirectives Directives bound to the base controller (App)
     *
     * @throws ControllerException
     */
    public function __registerDirectives(array $appDirectives)
    {
        if (!$this->class->implementsInterface('\Sober\Controller\Module\Tree') && $this->tree === false) {
            // The controller does not implement Tree, deregister all previous directives
            $this->__deregisterDirectives();
        }

        // Because the directives from base controller (App) might be unregistered from in one of the previous
        // iterations of parent controllers (some of which might not implement Tree interface)
        // we need to prepend the base controller directives
        self::$_activeDirectives = array_merge(
            // Make sure app directives do not override anything from the subsequent controllers
            $appDirectives,
            // Overwrite app directives with anything registered before
            self::$_activeDirectives,
            // Overwrite parent directives from current controller
            $this->directives
        );

        foreach (self::$_activeDirectives as $name => $method) {
            if (\in_array($name, self::$_restrictedBladeDirectives)) {
                throw new ControllerException('You cannot define static method "'.$method->name.'" on '.static::class.' as this conflicts with the default blade directive');
            }

            // Add the static methods as blade directives
            \App\sage('blade')->compiler()->directive(
                $name,
                function ($args) use ($method) {
                    return '<?= call_user_func_array(["'.$method->class.'", "'.$method->name.'"], ['.$args.']); ?>';
                }
            );
        }
    }

    /**
     * Deregisters active directives
     *
     * If an unregistered directive is called from a template an exception will be thrown
     *
     * When we inherit the hierarchy and do not implement Tree interface we do not want
     * the previously registered directives (from parent controllers) to work
     */
    private function __deregisterDirectives()
    {
        foreach (array_keys(self::$_activeDirectives) as $name) {
            \App\sage('blade')->compiler()->directive(
                $name,
                function () use ($name) {
                    return '<?php throw new \Sober\Controller\ControllerException("The directive @'.$name.' was not implemented in the current controller. Either implement the static method or the Sober\Controller\Module\Tree interface in '.static::class.'."); ?>';
                }
            );
        }

        // Reset the active directives
        self::$_activeDirectives = [];
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
     * Prepare the child class public static methods as blade directives
     */
    private function __runMethods()
    {
        foreach ($this->methods as $method) {
            if ($this->__isControllerMethod($method)) {
                continue;
            } elseif ($this->__isStaticMethod($method)) {
                $this->directives[$this->__sanitizeMethod($method->name)] = $method;
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

    /**
     * Returns directives
     *
     * @return array
     */
    public function __getDirectives()
    {
        return ($this->active ? $this->directives : array());
    }

    private function __clone() {}
    private function __wakeup() {}
}
