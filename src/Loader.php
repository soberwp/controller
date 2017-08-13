<?php

namespace Sober\Controller;

class Loader
{
    protected $path;
    protected $files;
    protected $instance;
    protected $instances = [];

    public function __construct()
    {
        $this->setPath();

        if (!file_exists($this->path)) {
            return;
        }

        $this->setDocumentClasses();
        $this->setFileList();
        $this->includeTraits();
        $this->includeClasses();
    }

    /**
     * Set Path
     *
     * Set the default path or get the custom path
     */
    protected function setPath()
    {
        $this->path = (has_filter('sober/controller/path') ? apply_filters('sober/controller/path', rtrim($this->path)) : dirname(get_template_directory()) . '/app/controllers');
    }

    /**
     * Set File List
     *
     * Recursively get file list and place into array
     */
    protected function setFileList()
    {
        $this->files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
    }

    /**
     * Set Classes to Body
     *
     * @return string
     */
    protected function setDocumentClasses()
    {
        add_filter('body_class', function ($body) {
            global $wp_query;
            $templates = (new \Brain\Hierarchy\Hierarchy())->getTemplates($wp_query);
            $templates = array_reverse($templates);
            $classes[] = 'app-data';

            foreach ($templates as $template) {
                if (strpos($template, '.blade.php') || $template === 'index.php') {
                    continue;
                }
                if ($template === 'index') {
                    $template = 'index.php';
                }
                $classes[] = basename(str_replace('.php', '-data', $template));
            }

            return array_merge($body, $classes);
        });
    }

    /**
     * Set Instance
     *
     * Add instance name and class to $instances[]
     */
    protected function setInstance()
    {
        $class = get_declared_classes();
        $class = '\\' . end($class);
        $template = pathinfo($this->instance, PATHINFO_FILENAME);
        // Convert camel case to match template
        $template = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $template));
        $this->instances[$template] = $class;
    }

    /**
     * Is File
     *
     * Determine if the file is a PHP file (excludes directories)
     * @return boolean
     */
    protected function isFile()
    {
        return (in_array(pathinfo($this->instance, PATHINFO_EXTENSION), ['php']));
    }

    /**
     * Is File Class
     *
     * Determine if the file is a Controller Class
     * @return boolean
     */
    protected function isFileClass()
    {
        return (strstr(file_get_contents($this->instance), "extends Controller") ? true : false);
    }

    /**
     * Return Base Data
     *
     * @return array
     */
    public function getAppData()
    {
        if (array_key_exists('app', $this->instances)) {
            $app = new $this->instances['app']();
            $app->__setup();
            return $app->__getData();
        }
        return array();
    }

    /**
     * Return Post Data
     *
     * @return array
     */
    public function getPostData()
    {
        if (is_singular()) {
            return array('post' => get_post());
        }
        return array();
    }

    /**
     * Return Data
     *
     * @return array
     */
    public function getData()
    {
        return $this->instances;
    }


    /**
     * Traits Loader
     *
     * Load each Trait instance
     */
    protected function includeTraits()
    {
        foreach ($this->files as $filename => $file) {
            $this->instance = $filename;
            if (!$this->isFile() || $this->isFileClass()) {
                continue;
            }
            include_once $filename;
        }
    }

    /**
     * Classes Loader
     *
     * Load each Class instance
     */
    protected function includeClasses()
    {
        foreach ($this->files as $filename => $file) {
            $this->instance = $filename;
            if (!$this->isFile() || !$this->isFileClass()) {
                continue;
            }
            include_once $filename;
            $this->setInstance();
        }
    }
}
