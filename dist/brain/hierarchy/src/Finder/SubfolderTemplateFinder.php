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
 * Very similar to the way WordPress core works, however, it allows to search
 * templates in a subfolder (for both parent and child themes) and to use one or
 * more custom file extensions (defaults to php).
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class SubfolderTemplateFinder implements TemplateFinderInterface
{
    /**
     * @var \Brain\Hierarchy\Finder\FoldersTemplateFinder
     */
    private $finder;

    /**
     * @param string       $subfolder
     * @param string|array $extension
     */
    public function __construct($subfolder, $extension = 'php')
    {
        $stylesheet = trailingslashit(get_stylesheet_directory()).$subfolder;
        $template = trailingslashit(get_template_directory()).$subfolder;
        $folders = [$stylesheet];
        ($stylesheet !== $template) and $folders[] = $template;

        $this->finder = new FoldersTemplateFinder($folders, $extension);
    }

    /**
     * {@inheritdoc}
     */
    public function find($template, $type)
    {
        return $this->finder->find($template, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function findFirst(array $templates, $type)
    {
        return $this->finder->findFirst($templates, $type);
    }
}
