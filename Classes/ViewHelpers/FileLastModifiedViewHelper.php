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
 * View helper that calculates the latest timestamp for files in a given folder.
 *
 * = Examples =
 *
 * <code title="Example">
 * <fl:fileLastModified folder="{folder}" recursive="1" />
 * </code>
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  tx_filelist
 * @author      Matthias Bernad <matthias.bernad@gmail.com>
 * @copyright   Matthias Bernad
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileLastModifiedViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * Returns the timestamp for the latest modified file inside the given folder.
     *
     * @param File|null $folder whose files should be counted
     * @param bool $recursive indicates whether files in subfolders should be counted too
     * @return int
     * @api
     */
    public function render($folder = null, $recursive = true)
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
     * Calls the recursive helper function calcLatestModifiedTimeStamp with the given arguments.
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

        return self::calcLatestModifiedTimeStamp($folder, $recursive);
    }

    /**
     * Helper function for recursive calling.
     * Returns the timestamp of the latest modified file in the given folder and, depending on variable $recursive
     * being set to true, does that for all subfolder recursively.
     *
     * @param File|null $folder whose files should be counted
     * @param bool $recursive indicates whether files in subfolders should be considered too
     * @return int
     */
    private static function calcLatestModifiedTimeStamp($folder = null, $recursive = true)
    {
        $files = $folder->getFiles();
        $fileTimeStamps = array_map(function ($element) {
            return $element->getProperties()['modification_date'];
        }, $files);

        /** @var int latestTimestamp */
        $latestTimeStamp = sizeof($fileTimeStamps) ? max($fileTimeStamps) : 0;

        if ($recursive) {
            foreach ($folder->getSubfolders() as $sf) {
                $latestTimeStamp = max($latestTimeStamp, self::calcLatestModifiedTimeStamp($sf, $recursive));
            }
        }

        return $latestTimeStamp;
    }

}
