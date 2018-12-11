<?php

namespace Sober\Controller\Module;

class Acf
{
    // Config
    protected $data = [];

    private $returnArrayFormat = false;

    /**
     * Construct
     *
     * Initialise the Loader methods
     */
    public function __construct()
    {
        $this->setReturnFilter();
    }

    /**
     * Set Return Filter
     *
     * Return filter sober/controller/acf-array
     */
    private function setReturnFilter()
    {
        $this->returnArrayFormat =
            (has_filter('sober/controller/acf/array')
            ? apply_filters('sober/controller/acf/array', $this->returnArrayFormat)
            : false);
    }

    /**
     * Set Data Return Format
     *
     * Return object from array if acf/array filter is not set to true
     */
    public function setDataReturnFormat()
    {
        if ($this->returnArrayFormat) {
            return;
        }

        if ($this->data) {
            foreach ($this->data as $key => $item) {
                $this->data[$key] = json_decode(json_encode($item));
            }
        }
    }

    /**
     * Set Data Options Page
     *
     * Set data from the options page
     */
    public function setDataOptionsPage()
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        if (get_fields('options')) {
            $this->data['acf_options'] = get_fields('options');
        }
    }

    /**
     * Set Data
     *
     * Set data from passed in field keys
     */
    public function setData($acf)
    {
        $query = get_queried_object();

        if (is_bool($acf)) {
            $this->data = get_fields($query);
        }

        if (is_string($acf)) {
            $this->data = [$acf => get_field($acf, $query)];
        }

        if (is_array($acf)) {
            foreach ($acf as $item) {
                $this->data[$item] = get_field($item, $query);
            }
        }
    }

    /**
     * Get Data
     *
     * Return the data
     * @return array
     */
    public function getData()
    {
        return is_array($this->data) ? $this->data : [];
    }
}
