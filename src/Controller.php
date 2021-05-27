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
    protected $post = false;
    protected $data = [];

    // Controller
    private $class;
    private $methods;
    private $dataMethods;
    private $staticMethods;

    // Loader
    private $incomingData;

    // Deps
    private $classAcf;

    /**
     * Before
     *
     * Lifecycle to interact with Controller after __setParams()
     */
    public function __before()
    {
    }

    /**
     * After
     *
     * Lifecycle to interact with Controller before __getData()
     */
    public function __after()
    {
    }

    /**
     * Set Params
     *
     * Set the Controller template and tree params
     */
    final public function __setParams()
    {
        // $this->class
        $this->class = new \ReflectionClass($this);

        // $this->classAcf
        if (class_exists('Acf')) {
            $this->classAcf = new Acf();
        }

        // $this->template
        if (!$this->template) {
            $this->template = Utils::convertToKebabCase($this->class->getShortName());
        }

        // $this->tree
        if ($this->class->implementsInterface('\Sober\Controller\Module\Tree')) {
            $this->tree = true;
        }
    }

    /**
     * Set Controller Data
     *
     * Set the Controller raw data for this Controller
     * @return $this
     */
    final public function __setData($incomingData)
    {
        $this->incomingData = $incomingData;
        
        // Set the data from the WordPress post if singular to $this->data
        $this->__setDataFromPost();

        // Set the data from Advanced Custom Fields to $this->data
        $this->__setDataFromModuleAcf();

        // Set incoming filter data from Sage to App before Debugger
        $this->__setDataFromFilter();

        // Set the public methods from the class to $this->methods
        $this->__setDataFromMethods();

        // Set debugger data first to use only the raw data from the Controller
        $this->__setBladeData();

        // Set app data to $this->data['__app'] or merge with current data
        $this->__setAppData();

        // Set tree data to $this->data['__tree'] or merge with current data
        $this->__setTreeData();
    }

    /**
     * Set Data From Post
     *
     * Set the WordPress post
     */
    private function __setDataFromPost()
    {
        // Only set data from $post to App class
        if ($this->template !== 'app') {
            return;
        }

        // Only continue if $post is available
        if (!is_singular()) {
            return;
        }

        // Set $this->post to allow users to use $this->post->post_title and others
        $this->post = get_post();

        // Set the post array to be included in $this->data
        $this->data['post'] = $this->post;
    }

        /**
     * Set Data From Module Acf
     *
     * Set the Advanced Custom Fields data automatically
     */
    private function __setDatafromModuleAcf()
    {
        // If $this->acf is not set then return
        if (!$this->acf) {
            return;
        }

        // Set the fields data passed in from Controller
        $this->classAcf->setData($this->acf);

        // Get the options page is $this->acf is set to true on App
        if ($this->template === 'app' && is_bool($this->acf)) {
            $this->classAcf->setDataOptionsPage();
        }

        // Deterime if acf/array filter is enabled and return correct format
        $this->classAcf->setDataReturnFormat();

        // If there is no data return
        if (!$this->classAcf->getData()) {
            return;
        }

        // Merge the data from Acf module
        $this->data = array_merge($this->data, $this->classAcf->getData());
    }

    /**
     * Set Sage Filter Data
     *
     * Merge $this->data with $this->incomingData as Sage filters run before -data class filters
     */
    private function __setDataFromFilter()
    {
        if ($this->template === 'app') {
            // Merge all incoming data from app to allow Sage add_filter support
            $this->data = array_merge($this->data, $this->incomingData);
        }
    }

    /**
     * Set Methods
     *
     * Set $this->methods with all public methods
     */
    private function __setDataFromMethods()
    {
        // Get all public methods from class
        $this->methods = $this->class->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Remove __contruct, __init, __finalize and this class methods from $this->methods
        $this->methods = array_filter($this->methods, function ($method) {
            return
                $method->class !== 'Sober\Controller\Controller' &&
                $method->name !== '__construct' &&
                $method->name !== '__before' &&
                $method->name !== '__after';
        });

        // Get all public static methods from class
        $this->staticMethods = $this->class->getMethods(\ReflectionMethod::IS_STATIC);

        // Remove $this->staticMethods from $this->methods using array_diff
        $this->dataMethods = array_diff($this->methods, $this->staticMethods);

        // Filter the remaining data methods
        $this->dataMethods = array_filter($this->dataMethods, function ($method) {
            return $method = $method->name;
        });

        // For each method convert method name to snake case and add to data[key => value]
        foreach ($this->dataMethods as $method) {
            // Convert method name to snake case
            $var = Utils::convertToSnakeCase($method->name);

            // Add var method name to data[]
            $this->data[$var] = $this->{$method->name}();
        }
    }

    /**
     * Set Blade Data
     *
     * Update $this->data with __blade
     */
    private function __setBladeData()
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
        $this->incomingData['__blade'][] =  $debugger;

        // Set the updated array to $this->data for @debug use
        $this->data['__blade'] = $this->incomingData['__blade'];
    }

    /**
     * Set App Data
     *
     * Update $this->data with __app
     */
    private function __setAppData()
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
    private function __setTreeData()
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
