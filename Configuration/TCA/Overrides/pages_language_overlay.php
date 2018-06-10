<?php

call_user_func(
    function ($extKey, $table) {
        // Add new page type as possible select item:
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
            $table,
            'doktype',
            [
                'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_be.xlf:page.name',
                \Sethorax\DirectContent\Configuration\ExtensionConfiguration::DOKTYPE,
                'apps-pagetree-page-directcontent'
            ],
            '1',
            'after'
        );
    },
    'directcontent',
    'pages_language_overlay'
);