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

use Brain\Hierarchy\Finder\FoldersTemplateFinder;
use Brain\Hierarchy\Finder\TemplateFinderInterface;
use Brain\Hierarchy\Loader\FileRequireLoader;
use Brain\Hierarchy\Loader\TemplateLoaderInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class QueryTemplate implements QueryTemplateInterface
{
    /**
     * @var \Brain\Hierarchy\Finder\TemplateFinderInterface
     */
    private $finder;

    /**
     * @var \Brain\Hierarchy\Loader\TemplateLoaderInterface
     */
    private $loader;

    /**
     * @param \Brain\Hierarchy\Loader\TemplateLoaderInterface|null $loader
     *
     * @return \Brain\Hierarchy\QueryTemplate
     */
    public static function instanceWithLoader(TemplateLoaderInterface $loader = null)
    {
        return new static(new FoldersTemplateFinder(), $loader);
    }

    /**
     * @param array                                                $folders
     * @param \Brain\Hierarchy\Loader\TemplateLoaderInterface|null $loader
     *
     * @return \Brain\Hierarchy\QueryTemplate
     */
    public static function instanceWithFolders(
        array $folders,
        TemplateLoaderInterface $loader = null
    ) {
        return new static(new FoldersTemplateFinder($folders), $loader);
    }

    /**
     * @return bool
     */
    public static function mainQueryTemplateAllowed()
    {
        return
            (
                filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'HEAD'
                || !apply_filters('exit_on_http_head', true)
            )
            && !is_robots()
            && !is_feed()
            && !is_trackback()
            && !is_embed();
    }

    /**
     * @param \Brain\Hierarchy\Finder\TemplateFinderInterface|null $finder
     * @param \Brain\Hierarchy\Loader\TemplateLoaderInterface      $loader
     */
    public function __construct(
        TemplateFinderInterface $finder = null,
        TemplateLoaderInterface $loader = null
    ) {
        // if no finder provided, let's use the one that simulates core behaviour
        $this->finder = $finder ?: new FoldersTemplateFinder();
        $this->loader = $loader ?: new FileRequireLoader();
    }

    /**
     * Find a template for the given WP_Query.
     * If no WP_Query provided, global \WP_Query is used.
     * By default, found template passes through "{$type}_template" filter.
     *
     * {@inheritdoc}
     */
    public function findTemplate(\WP_Query $query = null, $filters = true)
    {
        $leaves = (new Hierarchy())->getHierarchy($query);

        if (!is_array($leaves) || empty($leaves)) {
            return '';
        }

        $types = array_keys($leaves);
        $found = '';
        while (!empty($types) && !$found) {
            $type = array_shift($types);
            $found = $this->finder->findFirst($leaves[$type], $type);
            $filters and $found = $this->applyFilter("{$type}_template", $found, $query);
        }

        return $found;
    }

    /**
     * Find a template for the given query and load it.
     * If no WP_Query provided, global \WP_Query is used.
     * By default, found template passes through "{$type}_template" and "template_include" filters.
     *
     * {@inheritdoc}
     */
    public function loadTemplate(\WP_Query $query = null, $filters = true, &$found = false)
    {
        $template = $this->findTemplate($query, $filters);
        $filters and $template = $this->applyFilter('template_include', $template, $query);
        $found = is_file($template) && is_readable($template);

        return $found ? $this->loader->load($template) : '';
    }

    /**
     * To maximize compatibility, when applying a filters and the WP_Query object we are using is
     * NOT the main query, we temporarily set global $wp_query and $wp_the_query to our custom query.
     *
     * @param string    $filter
     * @param string    $value
     * @param \WP_Query $query
     *
     * @return string
     */
    private function applyFilter($filter, $value, \WP_Query $query = null)
    {
        $backup = [];
        global $wp_query, $wp_the_query;
        is_null($query) and $query = $wp_query;
        $custom = !$query->is_main_query();

        if ($custom && $wp_query instanceof \WP_Query && $wp_the_query instanceof \WP_Query) {
            $backup = [$wp_query, $wp_the_query];
            unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
            $GLOBALS['wp_query'] = $GLOBALS['wp_the_query'] = $query;
        }

        $result = apply_filters($filter, $value);

        if ($custom && $backup) {
            unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
            list($wpQuery, $wpTheQuery) = $backup;
            $GLOBALS['wp_query'] = $wpQuery;
            $GLOBALS['wp_the_query'] = $wpTheQuery;
        }

        return $result;
    }
}
