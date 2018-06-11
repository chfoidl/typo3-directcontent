<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function($extKey) {
    $directContentDoktype = \Sethorax\DirectContent\Configuration\ExtensionConfiguration::DOKTYPE;

    $GLOBALS['PAGES_TYPES'][$directContentDoktype] = [
        'type' => 'web',
        'allowedTables' => 'tt_content,pages',
    ];

    if (\Sethorax\DirectContent\Utility\Utility::getMajorTYPO3Version() < 9) {
        $GLOBALS['PAGES_TYPES'][$directContentDoktype]['allowedTables'] = $GLOBALS['PAGES_TYPES'][$directContentDoktype]['allowedTables'] . ',pages_language_overlay';
    }

    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)
        ->registerIcon(
            'apps-pagetree-page-directcontent',
            TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => 'EXT:' . $extKey . '/Resources/Public/Icons/apps-pagetree-page-directcontent.svg',
            ]
        );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $directContentDoktype . ')'
    );

    /**
     * Set default colPos for content elements
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'tx_' . $extKey . '.defaultValues.colPos = ' . \Sethorax\DirectContent\Configuration\ExtensionConfiguration::DEFAULT_COLPOS
    );

    /**
     * Register hooks
     */
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][$extKey] =
        \Sethorax\DirectContent\Hook\PageLayoutDrawHeaderHook::class . '->run';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook'][$extKey] =
        \Sethorax\DirectContent\Hook\GetButtonsHook::class . '->run';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] =
        \Sethorax\DirectContent\Hook\DatamapDataHandlerHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$extKey] =
        \Sethorax\DirectContent\Hook\CmdmapDataHandlerHook::class;
};

$boot('directcontent');
unset($boot);