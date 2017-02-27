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
final class BranchArchive implements BranchInterface
{
    /**
     * {@inheritdoc}
     */
    public function name()
    {
        return 'archive';
    }

    /**
     * {@inheritdoc}
     */
    public function is(\WP_Query $query)
    {
        return $query->is_archive();
    }

    /**
     * {@inheritdoc}
     */
    public function leaves(\WP_Query $query)
    {
        return ['archive'];
    }
}
