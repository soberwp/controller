<?php

namespace Sober\Controller\Module;

class Acf
{
    /**
     * Convert
     *
     * Return object from array
     * @return object
     */
    private static function convert($arr)
    {
        return json_decode(json_encode($arr));
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

    /**
     * Controller Module
     *
     * Return field values with first level of array as key
     * @return array
     */
    public static function getModuleData($acf)
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
