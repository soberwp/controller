<?php

namespace Sober\Controller;

class Utils
{
    /**
     * Is File PHP
     *
     * Determine if the file is a PHP file
     * @return boolean
     */
    public static function isFilePhp($filename)
    {
        return (in_array(pathinfo($filename, PATHINFO_EXTENSION), ['php']));
    }

    /**
     * Does File Contain
     *
     * Determine if the file contains a string
     * @return boolean
     */
    public static function doesFileContain($filename, $str)
    {
        return strpos(file_get_contents($filename), $str) !== false;
    }

    /**
     * Is Array Indexed
     *
     * Determine if the array is indexed
     * @return boolean
     */
    public static function isArrayIndexed(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Does String Contain Markup
     *
     * Determine if the string contains markup
     * @return boolean
     */
    public static function doesStringContainMarkup($str)
    {
        return (is_string($str) && $str !== strip_tags($str));
    }

    /**
     * Convert To Snake Case
     *
     * Convert camel case to snake case for data variables
     * @return string
     */
    public static function convertToSnakeCase($str)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    /**
     * Convert To Kebab Case
     *
     * Convert camel case to kebab case for templates
     * @return string
     */
    public static function convertToKebabCase($str)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $str));
    }
}
