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
final class BranchAttachment implements BranchInterface
{
    /**
     * {@inheritdoc}
     */
    public function name()
    {
        return 'attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function is(\WP_Query $query)
    {
        return $query->is_attachment();
    }

    /**
     * {@inheritdoc}
     */
    public function leaves(\WP_Query $query)
    {
        /** @var \WP_Post $post */
        $post = $query->get_queried_object();
        $post instanceof \WP_Post or $post = new \WP_Post((object) ['ID' => 0]);

        $leaves = [];
        empty($post->post_mime_type) or $mimetype = explode('/', $post->post_mime_type, 2);
        if (!empty($mimetype) && !empty($mimetype[0])) {
            $leaves = isset($mimetype[1]) && $mimetype[1]
                ? [$mimetype[0], $mimetype[1], "{$mimetype[0]}_{$mimetype[1]}"]
                : [$mimetype[0]];
        }

        $leaves[] = 'attachment';

        return $leaves;
    }
}
