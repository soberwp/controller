<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Loader;

/**
 * An aggregate loader where priority of loading for predicates is based on order of addition (FIFO).
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class CascadeAggregateTemplateLoader implements AggregateTemplateLoaderInterface
{
    /**
     * @var array
     */
    private $loaders = [];

    /**
     * {@inheritdoc}
     */
    public function addLoader(TemplateLoaderInterface $loader, callable $predicate)
    {
        $this->loaders[] = [$loader, $predicate, false];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addLoaderFactory(callable $loaderFactory, callable $predicate)
    {
        $this->loaders[] = [$loaderFactory, $predicate, true];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load($templatePath)
    {
        $loaders = $this->loaders;

        foreach ($loaders as $i => $loaderData) {
            list($loader, $predicate, $isFactory) = $loaderData;

            if (!$predicate($templatePath)) {
                continue;
            }

            if (!$isFactory) {
                /* @var \Brain\Hierarchy\Loader\TemplateLoaderInterface $loader */
                return $loader->load($templatePath);
            }

            $loader = $loader();

            if (!$loader instanceof TemplateLoaderInterface) {
                continue;
            }

            return $loader->load($templatePath);
        }

        return '';
    }
}
