<?php

namespace Sober\Controller\Module;

class Acf
{
    /**
     * Set Raw Filter
     *
     * Return filter sober/controller/acf-array
     * @return boolean
     */
    private static function setRawFilter()
    {
        $rawFilter = (has_filter('sober/controller/acf/array')
        ? apply_filters('sober/controller/acf/array', $rawFilter)
        : false);

        return $rawFilter;
    }

    /**
     * Convert
     *
     * Return object from array
     * @return object
     */
    private static function convert($arr)
    {
        if (!Acf::setRawFilter()) {
            $arr = json_decode(json_encode($arr));
        }
        return $arr;
    }

    /**
     * Get Fields
     *
     * Return field values from Acf
     * @return object
     */
    public static function get($items = null)
    {
        // Get all fields on page
        if ($items === null) {
            $data = Acf::convert(get_fields());
        }

        // Get field from string
        if (is_string($items)) {
            $data = Acf::convert(get_field($items));
        }

        // Get fields from array
        if (is_array($items)) {
            foreach ($items as $item) {
                $data[$item] = Acf::convert(get_field($item));
            }
        }

        // Return
        return $data;
    }

    public static function getOptions()
    {
        if (function_exists('acf_add_options_page')) {
            $options = Acf::convert(get_fields('options'));
        }

        return $options;
    }

    /**
     * Controller Module
     *
     * Return field values with first level of array as key
     * @return array
     */
    public static function getModuleData($acf, $options)
    {
        // If $acf is boolean set $acf to null to get all fields
        if (is_bool($acf)) {
            $acf = null;
        }

        // If $acf is string convert to array to get key included
        if (is_string($acf)) {
            $acf = [$acf];
        }

        // Get $acf items
        $items = Acf::get($acf);

        if ($options) {
            $items = array_merge(Acf::getOptions(), $items);
        }

        // Initialize data array
        $data = [];

        // Create an array for each item returned
        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $data[$key] = $item;
            }
        }

        // Return
        return $data;
    }
}
