<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    protected function hidden()
    {
        // protected methods will not be exposed to the blade template/s
    }

    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
    }
}