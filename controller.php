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
 * Initialise Loader class
 */
add_action('init', function () {
    $loader = new Loader();
    foreach ($loader->controllers() as $controller) {
        foreach ($controller['template'] as $name) {
            add_filter('sage/template/' . $name . '/data', function ($data) use ($controller) {
                return (new $controller['class'])->__controller();
            });
        }
    }
});

/**
 * Initialise Debugger class for Blade directive
 */
add_action('init', function () {
    // Debug
    \App\sage('blade')->compiler()->directive('debug', function () {
        return '<?php (new \Sober\Controller\Debugger(get_defined_vars()))->debug(); ?>';
    });
    // Controller
    \App\sage('blade')->compiler()->directive('controller', function () {
        return '<?php (new \Sober\Controller\Debugger(get_defined_vars()))->controller(); ?>';
    });
});
