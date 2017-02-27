<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Finder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
interface TemplateFinderInterface
{
    /**
     * Receives
     * - as first argument template name (no extension, no folder) something like
     *   'page', 'single', 'category-foo', 'index...
     * - as second argument the template "type", that represents `$type` part in all the 16
     *   "{$type}_template" hooks.
     *
     * Has to return full absolute template path or "" if not found.
     *
     * @param string $template
     * @param string $type
     *
     * @return string
     *
     * @link https://developer.wordpress.org/reference/hooks/type_template/
     */
    public function find($template, $type);

    /**
     * Similar to find(), but first argument is an array fo templates.
     * Has to return full absolute template path of the first template found, or "" if not found.
     *
     * @param array  $templates
     * @param string $type
     *
     * @return string
     */
    public function findFirst(array $templates, $type);
}
