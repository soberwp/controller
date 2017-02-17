<?php

namespace App;

use Sober\Controller\Controller;

class Images extends Controller
{
    public $templates = ['single', 'page'];

    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
    }
}
