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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @method string find(string $template, string $type)
 */
trait FindFirstTemplateTrait
{
    /**
     * @param array  $templates
     * @param string $type
     *
     * @return string
     *
     * @see \Brain\Hierarchy\Finder\TemplateFinderInterface::findFirst()
     */
    public function findFirst(array $templates, $type)
    {
        $found = '';
        while (!empty($templates) && $found === '') {
            $found = $this->find(array_shift($templates), $type) ?: '';
        }

        return $found;
    }
}
