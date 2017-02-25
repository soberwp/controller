<?php
/*
Plugin Name:        Controller
Plugin URI:         http://github.com/soberwp/controller
Description:        WordPress plugin to enable a basic controller when using Blade with Sage 9
Version:            1.0.0-alpha3
Author:             Sober
Author URI:         http://github.com/soberwp/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
GitHub Plugin URI:  soberwp/controller
GitHub Branch:      master
*/

namespace Sober\Controller;

use Sober\Controller\Loader\Config;
use Sober\Controller\Loader\Controller;

/**
 * Plugin
 */
if (!defined('ABSPATH')) {
    die;
};

require(file_exists($composer = __DIR__ . '/vendor/autoload.php') ? $composer : __DIR__ . '/dist/autoload.php');

/**
 * Functions
 */
function controllers()
{
    $controllers = (new Loader())->get();
    foreach ($controllers as $controller) {
        foreach ($controller['template'] as $route) {
            add_filter('sage/template/' . $route . '-controller/data', function ($data) use ($controller) {
                return (new $controller['class'])->__controller();
            });
        }
    }
}

function debugger()
{
    \App\sage('blade')->compiler()->directive('debug', function ($type) {
        $debugger = ($type === '' ? 'controller' : $type);
        return '<?php (new \Sober\Controller\Module\Debugger(get_defined_vars(), ' . $debugger . ')); ?>';
    });
}

/**
 * Hooks
 */
add_action('init', __NAMESPACE__ . '\controllers');
add_action('init', __NAMESPACE__ . '\debugger');
