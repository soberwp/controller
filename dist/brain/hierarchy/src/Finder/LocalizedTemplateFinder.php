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
 * Search templates looking for "localized" folders.
 *
 * Assuming
 *  - `$subfolder` is "templates"
 *  - `$extension` is "php"
 *  - current locale is "it_IT"
 *  - template to search is "page"
 *  - there is a child theme active
 *
 * It returns the first found among:
 *
 * 1. /path/to/child/theme/templates/it_IT/page.php
 * 2. /path/to/parent/theme/templates/it_IT/page.php
 * 3. /path/to/child/theme/templates/page.php
 * 4. /path/to/parent/theme/templates/page.php
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class LocalizedTemplateFinder implements TemplateFinderInterface
{
    use FindFirstTemplateTrait;

    /**
     * @var array
     */
    private $folders = [];

    /**
     * @var \Brain\Hierarchy\Finder\FoldersTemplateFinder
     */
    private $finder;

    /**
     * @param \Brain\Hierarchy\Finder\TemplateFinderInterface $finder
     */
    public function __construct(TemplateFinderInterface $finder = null)
    {
        $this->finder = $finder ?: new FoldersTemplateFinder();
        $locale = get_locale();
        if (!$locale || !is_string($locale)) {
            return;
        }
        $this->folders = [filter_var($locale, FILTER_SANITIZE_URL)];
        if (strpos($locale, '_') !== false) {
            $parts = explode('_', $locale, 2);
            $part = reset($parts);
            $part and $this->folders[] = filter_var($part, FILTER_SANITIZE_URL);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($template, $type)
    {
        if (empty($this->folders)) {
            return $this->finder->find($template, $type);
        }

        $templates = array_map(function ($folder) use ($template) {
            return $folder.'/'.$template;
        }, $this->folders);

        $templates[] = $template;

        return $this->finder->findFirst($templates, $type);
    }
}
