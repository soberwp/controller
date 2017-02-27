<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
interface QueryTemplateInterface
{
    /**
     * Find a template for the given WP_Query.
     * If no WP_Query provided, global \WP_Query is used.
     * By default, found template passes through "{$type}_template" filter.
     *
     * @param \WP_Query $query
     * @param bool      $filters Pass the found template through filter?
     *
     * @return string
     */
    public function findTemplate(\WP_Query $query = null, $filters = true);

    /**
     * Find a template for the given query and load it returning the results.
     *
     * @param \WP_Query|null $query
     * @param bool           $filters Pass the found template through filters?
     * @param bool           $found   It is set to true when template is found
     *
     * @return string
     */
    public function loadTemplate(\WP_Query $query = null, $filters = true, &$found = false);
}
