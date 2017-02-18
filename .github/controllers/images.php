<?php

namespace App;

use Sober\Controller\Controller;

class Images extends Controller
{
    public $templates = ['single', 'page'];

    /**
     * Return images from Advanced Custom Fields
     *
     * @return array
     */
    public function images()
    {
        return get_field('images');
    }
}
