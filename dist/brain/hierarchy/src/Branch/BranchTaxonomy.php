<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Branch;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchTaxonomy implements BranchInterface
{
    /**
     * {@inheritdoc}
     */
    public function name()
    {
        return 'taxonomy';
    }

    /**
     * {@inheritdoc}
     */
    public function is(\WP_Query $query)
    {
        return $query->is_tax();
    }

    /**
     * {@inheritdoc}
     */
    public function leaves(\WP_Query $query)
    {
        /** @var \stdClass $term */
        $term = $query->get_queried_object();

        if (!isset($term->slug) || !isset($term->taxonomy)) {
            return ['taxonomy'];
        }

        return [
            "taxonomy-{$term->taxonomy}-{$term->slug}",
            "taxonomy-{$term->taxonomy}",
            'taxonomy',
        ];
    }
}
