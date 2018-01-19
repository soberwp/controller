<?php

namespace Sober\Controller;

use Brain\Hierarchy\Hierarchy;

use function App\sage;

/**
 * Loader
 */
function loader()
{
    if (!function_exists('\App\sage')) {
        return;
    }

    // Run WordPress hierarchy class
    $hierarchy = new Hierarchy();

    // Run Loader class and pass on WordPress hierarchy class
    $loader = new Loader($hierarchy);

    // Use the Sage DI container
    $container = sage();

    // Loop over each class
    foreach ($loader->getClassesToRun() as $class) {
        // Create the class on the DI container
        $controller = $container->make($class);

        // Set the params required for location
        $controller->__setParams();

        // Determine template location to expose data
        $location = 'sage/template/' . $controller->__getTemplateParam() . '-data/data';

        // Pass data to filter
        add_filter($location, function ($data) use ($class, $controller) {
            // Return the data
            return $controller
                ->__setControllerData()
                ->__setIncomingData($data)
                ->__getData();
        });
    }
}

/**
 * Debugger
 */
function debugger()
{
    if (!function_exists('\App\sage')) {
        return;
    }

    sage('blade')->compiler()->directive('debug', function () {
        return '<?php (new \Sober\Controller\Debugger(get_defined_vars())); ?>';
    });

    sage('blade')->compiler()->directive('dump', function ($param) {
        return "<?php (new Illuminate\Support\Debug\Dumper)->dump($param); ?>";
    });
}

/**
 * Hooks
 */
if (function_exists('add_action')) {
    add_action('init', __NAMESPACE__ . '\loader');
    add_action('init', __NAMESPACE__ . '\debugger');
}
