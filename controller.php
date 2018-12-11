<?php

namespace Sober\Controller;

use Brain\Hierarchy\Hierarchy;

/**
 * Sage Namespace
 */
function sage()
{
    // Determine if project namespace has been changed
    $sage = apply_filters('sober/controller/sage/namespace', 'App') . '\sage';

    // Return the function if it exists
    if (function_exists($sage)) {
        return $sage;
    }

    // Return false if function does not exist
    return false;
}

/**
 * Loader
 */
function loader()
{
    // Get Sage function
    $sage = sage();

    // Return if function does not exist
    if (!$sage) {
        return;
    }

    // Run WordPress hierarchy class
    $hierarchy = new Hierarchy();

    // Run Loader class and pass on WordPress hierarchy class
    $loader = new Loader($hierarchy);

    // Use the Sage DI container
    $container = $sage();

    // Loop over each class
    foreach ($loader->getClassesToRun() as $class) {
        // Create the class on the DI container
        $controller = $container->make($class);

        // Set the params required for template param
        $controller->__setParams();

        // Determine template location to expose data
        $location = "sage/template/{$controller->__getTemplateParam()}-data/data";

        // Pass data to filter
        add_filter($location, function ($data) use ($container, $class) {
            // Recreate the class so that $post is included
            $controller = $container->make($class);

            // Params
            $controller->__setParams();

            // Lifecycle
            $controller->__before();

            // Data
            $controller->__setData($data);

            // Lifecycle
            $controller->__after();

            // Return
            return $controller->__getData();
        }, 10, 2);
    }
}

/**
 * Blade
 */
function blade()
{
    // Get Sage function
    $sage = sage();

    // Return if function does not exist
    if (!$sage) {
        return;
    }

    // Debugger
    $sage('blade')->compiler()->directive('debug', function () {
        return '<?php (new \Sober\Controller\Blade\Debugger(get_defined_vars())); ?>';
    });

    $sage('blade')->compiler()->directive('dump', function ($param) {
        return "<?php (new Illuminate\Support\Debug\Dumper)->dump({$param}); ?>";
    });

    // Coder
    $sage('blade')->compiler()->directive('code', function ($param) {
        $param = ($param) ? $param : 'false';
        return "<?php (new \Sober\Controller\Blade\Coder(get_defined_vars(), {$param})); ?>";
    });

    $sage('blade')->compiler()->directive('codeif', function ($param) {
        $param = ($param) ? $param : 'false';
        return "<?php (new \Sober\Controller\Blade\Coder(get_defined_vars(), {$param}, true)); ?>";
    });
}

/**
 * Hooks
 */
if (function_exists('add_action')) {
    add_action('init', __NAMESPACE__ . '\loader');
    add_action('init', __NAMESPACE__ . '\blade');
}
