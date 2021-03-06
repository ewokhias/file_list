<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

    /* ===========================================================================
        Legacy plugin (pibase-based)
    =========================================================================== */
    if (!isset($settings['enableLegacyPlugin']) || (bool)$settings['enableLegacyPlugin']) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
            $_EXTKEY,
            'Classes/Controller/Pi1/Pi1Controller.php',
            '_pi1',
            'list_type',
            $settings['noCache'] ? 0 : 1
        );
    }

    /* ===========================================================================
        Extbase-based plugin
    =========================================================================== */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Causal.' . $_EXTKEY,
        'Filelist',
        // cacheable actions
        [
            'File' => 'list',
        ],
        // non-cacheable actions
        []
    );

    /* ===========================================================================
        Web > Page hook
    =========================================================================== */
    $extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
    $pluginSignature = strtolower($extensionName) . '_filelist';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][$pluginSignature][$_EXTKEY] =
        \Causal\FileList\Hooks\PageLayoutView::class . '->getExtensionSummary';

    /* ===========================================================================
        File > Filelist signals
    =========================================================================== */
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

    $listenSignals = [
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileAdd,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileCopy,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileMove,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileRename,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileReplace,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileCreate,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileDelete,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileSetContents,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderAdd,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderCopy,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderMove,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderRename,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderDelete,
    ];
    foreach ($listenSignals as $signal) {
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Core\Resource\ResourceStorage::class,
            $signal,
            \Causal\FileList\Slots\ResourceStorage::class,
            $signal
        );
    }

    /* ===========================================================================
        Register default template layouts
    =========================================================================== */
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['file_list']['templateLayouts'][] = [
        'LLL:EXT:file_list/Resources/Private/Language/locallang_flexform.xlf:filelist.templateLayout.simple',
        'Simple',
    ];
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['file_list']['templateLayouts'][] = [
        'LLL:EXT:file_list/Resources/Private/Language/locallang_flexform.xlf:filelist.templateLayout.thumbnailDescription',
        'ThumbnailDescription',
    ];

};

$boot($_EXTKEY);
unset($boot);
