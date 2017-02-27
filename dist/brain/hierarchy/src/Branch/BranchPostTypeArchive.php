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
final class BranchPostTypeArchive implements BranchInterface
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
        return $query->is_post_type_archive() && $this->postType($query);
    }

    /**
     * {@inheritdoc}
     */
    public function leaves(\WP_Query $query)
    {
        $type = $this->postType($query);

        return $type ? ["archive-{$type}", 'archive'] : ['archive'];
    }

    /**
     * @param \WP_Query $query
     *
     * @return mixed|string
     */
    private function postType(\WP_Query $query)
    {
        $type = $query->get('post_type');
        is_array($type) and $type = reset($post_type);

        $object = get_post_type_object($type);
        (is_object($object) && $object->has_archive) or $type = '';

        return $type;
    }
}
