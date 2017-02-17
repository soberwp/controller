<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    /**
     * Protected and Private methods will not be passed to the template
     */
    protected function hidden()
    {
        
    }

    /**
     * Return images from Advanced Custom Fields
     *
     * @return array
     */
    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
    }
}