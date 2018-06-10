<?php

call_user_func(
    function ($extKey, $table) {
        $directContentDoktype = \Sethorax\DirectContent\Configuration\ExtensionConfiguration::DOKTYPE;

        // Add new page type as possible select item:
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
            $table,
            'doktype',
            [
                'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_be.xlf:page.name',
                $directContentDoktype,
                'apps-pagetree-page-directcontent'
            ],
            '1',
            'after'
        );

        // Add icon for new page type:
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
            $GLOBALS['TCA']['pages'],
            [
                'ctrl' => [
                    'typeicon_classes' => [
                        $directContentDoktype => 'apps-pagetree-page-directcontent',
                    ],
                ],
            ]
        );
    },
    'directcontent',
    'pages'
);