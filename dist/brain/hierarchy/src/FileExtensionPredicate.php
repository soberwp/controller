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
class FileExtensionPredicate
{
    /**
     * @var string[]
     */
    private $extension = [];

    /**
     * @param string|string[] $extensions
     * @param string          $trimPattern
     *
     * @return \string[]
     */
    public static function parseExtensions($extensions, $trimPattern = ". \t\n\r\0\x0B")
    {
        $parsed = [];
        $extensions = is_string($extensions) ? explode('|', $extensions) : (array) $extensions;
        foreach ($extensions as $extension) {
            if (is_string($extension)) {
                $extension = strtolower(trim($extension, $trimPattern));
                in_array($extension, $parsed, true) or $parsed[] = $extension;
            }
        }

        return $parsed;
    }

    /**
     * @param string|string[] $extension
     */
    public function __construct($extension)
    {
        $this->extension = self::parseExtensions($extension);
    }

    /**
     * @param string $templatePath
     *
     * @return bool
     */
    public function __invoke($templatePath)
    {
        $ext = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));

        return in_array($ext, $this->extension, true);
    }
}
