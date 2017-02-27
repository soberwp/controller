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
class Hierarchy
{

    const FILTERABLE = 1;
    const NOT_FILTERABLE = 2;

    /**
     * @var int
     */
    private $flags = 0;

    /**
     * @var array
     */
    private static $branches = [
        'embed'             => Branch\BranchEmbed::class,
        '404'               => Branch\Branch404::class,
        'search'            => Branch\BranchSearch::class,
        'frontpage'         => Branch\BranchFrontPage::class,
        'home'              => Branch\BranchHome::class,
        'post-type-archive' => Branch\BranchPostTypeArchive::class,
        'taxonomy'          => Branch\BranchTaxonomy::class,
        'attachment'        => Branch\BranchAttachment::class,
        'single'            => Branch\BranchSingle::class,
        'page'              => Branch\BranchPage::class,
        'singular'          => Branch\BranchSingular::class,
        'category'          => Branch\BranchCategory::class,
        'tag'               => Branch\BranchTag::class,
        'author'            => Branch\BranchAuthor::class,
        'date'              => Branch\BranchDate::class,
        'archive'           => Branch\BranchArchive::class,
        'paged'             => Branch\BranchPaged::class,
    ];

    /**
     * @param int $flags
     */
    public function __construct($flags = self::FILTERABLE)
    {
        $this->flags = is_int($flags) ? $flags : 0;
    }

    /**
     * Get hierarchy.
     *
     * @param \WP_Query $query
     *
     * @return array
     */
    public function getHierarchy(\WP_Query $query = null)
    {
        return $this->parse($query)->hierarchy;
    }

    /**
     * Get flatten hierarchy.
     *
     * @param \WP_Query $query
     *
     * @return array
     */
    public function getTemplates(\WP_Query $query = null)
    {
        return $this->parse($query)->templates;
    }

    /**
     * Parse all branches.
     *
     * @param \WP_Query $query
     *
     * @return \stdClass
     */
    private function parse(\WP_Query $query = null)
    {
        (is_null($query) && isset($GLOBALS['wp_query'])) and $query = $GLOBALS['wp_query'];

        $data = (object)['hierarchy' => [], 'templates' => [], 'query' => $query];

        $branches = self::$branches;

        // make the branches filterable, but assuring each item still implement branch interface
        if ($this->flags & self::FILTERABLE) {
            $branches = array_filter(
                (array)apply_filters('brain.hierarchy.branches', $branches),
                function ($branch) {
                    return is_subclass_of($branch, Branch\BranchInterface::class, true);
                }
            );
        }

        // removed indexes, we added them to make filtering easier
        $branches = array_values($branches);

        if ($query instanceof \WP_Query) {
            $data = array_reduce($branches, [$this, 'parseBranch'], $data);
        }

        $data->hierarchy = $this->addIndexLeaves($data->hierarchy);
        $data->templates[] = 'index';
        $data->templates = array_values(array_unique($data->templates));

        return $data;
    }

    /**
     * @param array $hierarchy
     * @return array
     */
    private function addIndexLeaves(array $hierarchy)
    {
        if (($this->flags & self::FILTERABLE) <= 0) {
            $hierarchy['index'] = ['index'];

            return $hierarchy;
        }

        $index_leaves = (array)apply_filters("index_template_hierarchy", ['index']);
        $index_leaves = array_filter($index_leaves, 'is_string');

        $hierarchy['index'] = array_filter($index_leaves);

        return $hierarchy;
    }

    /**
     * @param string    $branchClass
     * @param \stdClass $data
     *
     * @return \stdClass
     */
    private function parseBranch(\stdClass $data, $branchClass)
    {
        /** @var \Brain\Hierarchy\Branch\BranchInterface $branch */
        $branch = new $branchClass();
        $name = $branch->name();
        $isFilterable = ($this->flags & self::FILTERABLE) > 0;
        // When branches are filterable, we need this for core compatibility.
        $isFilterable and $name = preg_replace('|[^a-z0-9-]+|', '', $name);
        if ($branch->is($data->query) && ! isset($data->hierarchy[$name])) {
            $leaves = $branch->leaves($data->query);
            // this filter was introduced in WP 4.7
            $isFilterable and $leaves = apply_filters("{$name}_template_hierarchy", $leaves);
            $data->hierarchy[$name] = $leaves;
            $data->templates = array_merge($data->templates, $leaves);
        }

        return $data;
    }
}
