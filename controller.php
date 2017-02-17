<?php
/*
Plugin Name:        Controller
Plugin URI:         http://github.com/soberwp/controller
Description:        WordPress plugin to enable a basic controller when using Blade with Sage 9
Version:            1.0.0-alpha1
Author:             Sober
Author URI:         http://github.com/soberwp/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
GitHub Plugin URI:  soberwp/controller
GitHub Branch:      master
*/
namespace Sober\Controller;

/**
 * Restrict direct access to file
 */
if (!defined('ABSPATH')) {
    die;
}

/**
 * Require Composer PSR-4 autoloader, fallback dist/autoload.php
 */
if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require $composer;
} else {
    require __DIR__ . '/dist/autoload.php';
}

/**
 * Filter body_class to include global
 */
add_filter('body_class', function($classes) {
    return array_merge($classes, ['global']);
});

/**
 * Hook into add_action and initialise Loader class
 */
add_action('init', function () {
    $instances = (new Loader())->controllers();
    foreach ($instances as $instance) {
        foreach ($instance['template'] as $name) {
            add_filter('sage/template/' . $name . '/data', function ($data) use ($instance) {
                return (new $instance['class'])->controller();
            });
        }
    }
});
