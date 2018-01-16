<?php

namespace Sober\Controller;

use Sober\Controller\Utils;
use Sober\Controller\Module\Acf;

class Controller
{
    // Config
    protected $active = true;
    protected $template = false;
    protected $tree = false;
    protected $acf = false;

    // Controller
    private $class;
    private $methods;
    private $dataMethods;
    private $staticMethods;
    private $data = [];

    // Loader
    private $incomingData;

    /**
     * Set Params
     *
     * Set the Controller template and tree params
     */
    final public function __setParams()
    {
        // Create a reflection class for retrieving class name and methods
        $this->class = new \ReflectionClass($this);

        // Set the default template using class name
        $this->__setTemplateParam();

        // Set the tree to true if implements is used on class
        $this->__setTreeParam();
    }

    /**
     * Set Controller Data
     *
     * Set the Controller raw data for this Controller
     * @return $this
     */
    final public function __setControllerData()
    {
        // Set the public methods from the class to $this->methods
        $this->__setPublicMethods();

        // Remove __construct and Controller methods from $this->methods
        $this->__removeInternalMethods();

        // Set the public static methods to $this->staticMethods
        $this->__setStaticMethods();

        // Set the public non-static methods to $this->dataMethods
        $this->__setDataMethods();

        // Set the data from the WordPress post if singular to $this->data
        $this->__setDataFromPost();

        // Set the data from Advanced Custom Fields to $this->data
        $this->__setDataFromModuleAcf();

        // Convert data method names to snake case and set to $this->data
        $this->__setDataFromDataMethods();

        // Return
        return $this;
    }

    /**
     * Set Incoming Data
     *
     * Set the Controller meta data passed in from previous Controllers
     * @return $this
     */
    final public function __setIncomingData($incomingData)
    {
        $this->incomingData = $incomingData;

        // Set debugger data first to use only the raw data from the Controller
        $this->__setDebuggerData();

        // Set app data to $this->data['__app'] or merge with current data
        $this->__setAppData();

        // Set tree data to $this->data['__tree'] or merge with current data
        $this->__setTreeData();

        // Return
        return $this;
    }

    /**
     * Set Template Param
     *
     * Set this->template using the class short name converted to kebab case
     */
    final private function __setTemplateParam()
    {
        if (!$this->template) {
            $this->template = Utils::convertToKebabCase($this->class->getShortName());
        }
    }

    /**
     * Set Tree Param
     *
     * Set $this->tree if the class has used the Tree interface
     */
    final private function __setTreeParam()
    {
        if ($this->class->implementsInterface('\Sober\Controller\Module\Tree')) {
            $this->tree = true;
        }
    }

    /**
     * Set Methods
     *
     * Set $this->methods with all public methods
     */
    final private function __setPublicMethods()
    {
        // Get all public methods from class
        $this->methods = $this->class->getMethods(\ReflectionMethod::IS_PUBLIC);
    }

    /**
     * Remove Internal Methods
     *
     * Remove internal class methods from $this->methods
     */
    final private function __removeInternalMethods()
    {
        // Remove Controller methods from $this->methods
        $this->methods = array_filter($this->methods, function ($method) {
            return $method->class !== 'Sober\Controller\Controller';
        });

        // Remove __contruct method from $this->methods
        $this->methods = array_filter($this->methods, function ($method) {
            return $method->name !== '__construct';
        });
    }

    /**
     * Set Static Methods
     *
     * Set $this->staticMethods with public static methods
     */
    final private function __setStaticMethods()
    {
        // Get all public static methods from class
        $this->staticMethods = $this->class->getMethods(\ReflectionMethod::IS_STATIC);
    }

    /**
     * Set Data Methods
     *
     * Set $this->dataMethods with public non-static methods
     */
    final private function __setDataMethods()
    {
        // Remove $this->staticMethods from $this->methods using array_diff
        $this->dataMethods = array_diff($this->methods, $this->staticMethods);

        // Filter the remaining data methods
        $this->dataMethods = array_filter($this->dataMethods, function ($method) {
            return $method = $method->name;
        });
    }

    /**
     * Set Data From Public Methods
     *
     * For each method convert method name to snake case and add to data[key => value]
     */
    final private function __setDataFromDataMethods()
    {
        foreach ($this->dataMethods as $method) {
            // Convert method name to snake case
            $var = Utils::convertToSnakeCase($method->name);

            // Add var method name to data[]
            $this->data[$var] = $this->{$method->name}();
        }
    }

    /**
     * Set Data From Post
     *
     * Set the WordPress post
     */
    final private function __setDataFromPost()
    {
        // Only set data from $post to App class
        if ($this->template !== 'app') {
            return;
        }

        // Only continue if $post is available
        if (!is_singular()) {
            return;
        }

        // Set the post array to be included in $this->data
        $this->data['post'] = get_post();
    }

    /**
     * Set Data From Module Acf
     *
     * Set the Advanced Custom Fields data automatically
     */
    final private function __setDatafromModuleAcf()
    {
        if ($this->acf) {
            // Fetch current page Acf data and merge with $this->data
            $this->data = array_merge($this->data, Acf::getModuleData($this->acf));
        }
    }

    /**
     * Set Debugger Data
     *
     * Update $this->data with __debugger
     */
    final private function __setDebuggerData()
    {
        // Get the data
        $debuggerData = $this->data;

        // Loop through each data method
        foreach ($this->dataMethods as $dataMethod) {
            // Convert the key to snake case to find in $debuggerData
            $key = Utils::convertToSnakeCase($dataMethod->name);
            // Save the returned value from the above key
            $returned = $debuggerData[$key];
            // Recreate the key with the method included
            $debuggerData[$key] = (object) [
                'method' => $dataMethod,
                'returned' => $returned
            ];
        }

        // Create the final debugger object
        $debugger = (object) [
            'class' => $this->class->getShortName(),
            'tree' => $this->tree,
            'methods' => $this->staticMethods,
            'data' => $debuggerData
        ];

        // Include current debugger data in existing debugger array
        $this->incomingData['__debugger'][] =  $debugger;

        // Set the updated array to $this->data for @debug use
        $this->data['__debugger'] = $this->incomingData['__debugger'];
    }

    /**
     * Set App Data
     *
     * Update $this->data with __app
     */
    final private function __setAppData()
    {
        if ($this->template === 'app') {
            // Save the app data in $this->data['__app']
            $this->data['__app'] = $this->data;
            // Esc the function
            return;
        }

        // Save the app data to this $this->data['__app'] for next Controller
        $this->data['__app'] = $this->incomingData['__app'];

        // Include the app data with this current items data
        $this->data = array_merge($this->data['__app'], $this->data);
    }

    /**
     * Set Tree Data
     *
     * Update $this->data with tree data if required and store existing data
     */
    final private function __setTreeData()
    {
        if ($this->tree) {
            // Include existing data with this Controller data
            $this->data = array_merge($this->incomingData['__store'], $this->data);
        }

        // Save updated data to $this->data['__store'] for next Controller
        $this->data['__store'] = array_merge($this->incomingData, $this->data);
    }

    /**
     * Get Template Param
     *
     * @return string
     */
    final public function __getTemplateParam()
    {
        return ($this->active ? $this->template : false);
    }

    /**
     * Get Controller Data
     *
     * @return array
     */
    final public function __getData()
    {
        return ($this->active ? $this->data : []);
    }
}
