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

        if (!file_exists($this->path)) return;

        $this->getFiles();
        $this->run();
    }

    /**
     * Get Files
     *
     * Set the list of files
     */
    protected function getFiles()
    {
        $this->files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
    }

    /**
     * Is File Extension
     *
     * Check if the file extension is PHP
     * @return boolean
     */
    protected function isFileExtension($extensions)
    {
        $path = pathinfo($this->instance, PATHINFO_EXTENSION);
        return (in_array($path, $extensions));
    }
}
