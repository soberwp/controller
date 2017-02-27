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

use Symfony\Component\Finder\Finder;

/**
 * A Symfony Finder adapter. Hierarchy does not ship with Symfony Finder (only on development)
 * so it have to be installed separately.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class SymfonyFinderAdapter implements TemplateFinderInterface
{
    use FindFirstTemplateTrait;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    private $finder;

    /**
     * @param \Symfony\Component\Finder\Finder $finder
     */
    public function __construct(Finder $finder = null)
    {
        if (is_null($finder)) {
            $stylesheet = trailingslashit(get_stylesheet_directory());
            $template = trailingslashit(get_template_directory());
            $folders = [$stylesheet];
            ($stylesheet !== $template) and $folders[] = $template;
            $finder = (new Finder())
                ->in($folders)
                ->ignoreDotFiles(true)
                ->ignoreUnreadableDirs(true)
                ->followLinks();
        }

        $this->finder = $finder;
    }

    /**
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return \Brain\Hierarchy\Finder\SymfonyFinderAdapter
     */
    public function withSymfonyFinder(Finder $finder)
    {
        $clone = clone $this;
        $clone->finder = $finder;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function find($template, $type)
    {
        $name = trim(str_replace('\\', '/', $template), '/');
        $depth = substr_count($name, '/');

        $finder = clone $this->finder;
        $finder = $finder->depth("== {$depth}");

        if ($depth) {
            $dir = dirname($name);
            $finder = $finder->path($dir);
            $name = basename($name);
        }

        $quotedName = preg_quote($name, '~');
        /** @var \Iterator $iterator */
        $iterator = $finder->files()->name("~^{$quotedName}(\.[\w]{1,})?$~")->getIterator();

        if (!iterator_count($iterator) > 0) {
            return '';
        }

        $array = iterator_to_array($iterator);

        return reset($array)->getRealPath();
    }
}
