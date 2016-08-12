<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\FileList\ViewHelpers;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * View helper for counting files in a given folder.
 *
 * = Examples =
 *
 * <code title="Example">
 * <fl:fileCount folder="{folder}" recursive="1" />
 * </code>
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  tx_filelist
 * @author      Matthias Bernad <matthias.bernad@gmail.com>
 * @copyright   Matthias Bernad
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileCountViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * Returns the file count of the given folder.
     *
     * @param File|null $folder whose files should be counted
     * @param bool $recursive indicates whether files in subfolders should be counted too
     * @return int
     * @api
     */
    public function render($folder = null, $recursive = false)
    {
        return static::renderStatic(
            array(
                'folder' => $folder,
                'recursive' => $recursive
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Calls the recursive helper function countFiles with the given arguments.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return int
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var File $folder */
        $folder = $arguments['folder'];
        if ($folder === null) {
            $folder = $renderChildrenClosure();
        }

        $recursive = $arguments['recursive'];

        return self::countFiles($folder, $recursive);
    }

    /**
     * Helper function for recursive calling.
     * Counts the files in the given folder and, depending on variable $recursive
     * being set to true, adds the file count of all subfolder recursively.
     *
     * @param File|null $folder whose files should be counted
     * @param bool $recursive indicates whether files in subfolders should be counted too
     * @return int
     */
    private static function countFiles($folder = null, $recursive = false)
    {
        /** @var int $count */
        $count = count($folder->getFiles());

        if ($recursive) {
            foreach ($folder->getSubfolders() as $sf) {
                $count += self::countFiles($sf, $recursive);
            }
        }

        return $count;
    }

}
