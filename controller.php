<?php
/*
Plugin Name:        Controller
Plugin URI:         http://github.com/soberwp/controller
Description:        WordPress plugin to enable a basic controller when using Blade with Sage 9
Version:            1.0.1
Author:             Sober
Author URI:         http://github.com/soberwp/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
GitHub Plugin URI:  soberwp/controller
GitHub Branch:      master
*/

namespace Sober\Controller;

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
function loader()
{
    $loader = new Loader();
    foreach ($loader->getData() as $template => $class) {
        add_filter('sage/template/' . $template . '-data/data', function ($data) use ($loader, $class) {
            $controller = new $class();
            return array_merge($loader->getBaseData(), $loader->getPostData(), $controller->__setTreeData($data), $controller->__getData());
        });
    }
}

function debugger()
{
    if (function_exists('\\App\\sage')) {
        \App\sage('blade')->compiler()->directive('debug', function ($type) {
            $debugger = ($type === '' ? '"controller"' : $type);
            return '<?php (new \Sober\Controller\Module\Debugger(get_defined_vars(), ' .  $debugger . ')); ?>';
        });
    }
}

/**
 * Hooks
 */
add_action('init', __NAMESPACE__ . '\loader');
add_action('init', __NAMESPACE__ . '\debugger');
