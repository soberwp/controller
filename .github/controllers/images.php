<?php

namespace App;

use Sober\Controller\Controller;

class Images extends Controller
{
    public $templates = ['single', 'page'];

    /**
     * Return images from Advanced Custom Fields
     */
    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
    }
}
