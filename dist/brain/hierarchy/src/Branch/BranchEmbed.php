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
final class BranchEmbed implements BranchInterface
{
    /**
     * {@inheritdoc}
     */
    public function name()
    {
        return 'embed';
    }

    /**
     * {@inheritdoc}
     */
    public function is(\WP_Query $query)
    {
        return $query->is_embed();
    }

    /**
     * {@inheritdoc}
     */
    public function leaves(\WP_Query $query)
    {
        $post = $query->get_queried_object();
        $leaves = [];

        if ($post instanceof \WP_Post) {
            $post_format = get_post_format($post);
            $post_format and $leaves[] = "embed-{$post->post_type}-{$post_format}";
            $leaves[] = "embed-{$post->post_type}";
        }

        $leaves[] = 'embed';

        return $leaves;
    }
}
