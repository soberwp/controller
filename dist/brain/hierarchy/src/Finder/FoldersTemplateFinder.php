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

use Brain\Hierarchy\FileExtensionPredicate;

/**
 * Very similar to the way WordPress core works, however, it allows to search
 * templates within arbitrary folders and to use one or more custom file
 * extensions. By default, it looks through stylesheet and template folders and
 * allows file extension to be php, so it acts exactly like core.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FoldersTemplateFinder implements TemplateFinderInterface
{
    use FindFirstTemplateTrait;

    /**
     * @var \ArrayIterator
     */
    private $folders;

    /**
     * @var string|array
     */
    private $extensions;

    /**
     * @param array        $folders
     * @param string|array $extension
     */
    public function __construct(array $folders = [], $extension = 'php')
    {
        if (empty($folders)) {
            $stylesheet = get_stylesheet_directory();
            $template = get_template_directory();
            $folders = [$stylesheet];
            ($stylesheet !== $template) and $folders[] = $template;
        }

        $this->folders = array_map('trailingslashit', $folders);
        $this->extensions = FileExtensionPredicate::parseExtensions($extension);
    }

    /**
     * {@inheritdoc}
     */
    public function find($template, $type)
    {
        foreach ($this->folders as $folder) {
            foreach ($this->extensions as $extension) {
                $path = $extension ? $folder.$template.'.'.$extension : $folder.$template;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return '';
    }
}
