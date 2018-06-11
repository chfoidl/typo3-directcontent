<?php

namespace Sethorax\DirectContent\Hook;

use Sethorax\DirectContent\Configuration\ExtensionConfiguration;
use Sethorax\DirectContent\Utility\ContentUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

class PageLayoutDrawHeaderHook
{
    public function run(array $params, PageLayoutController $pageLayoutController)
    {
        $doktype = $pageLayoutController->pageinfo['doktype'];

        if ($doktype === ExtensionConfiguration::DOKTYPE) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $existingRecord = ContentUtility::fetchRecordsForPage($pageLayoutController->id);

            if (isset($existingRecord[0])) {
                $routeConf = [
                    'edit' => [
                        'tt_content' => [
                            $existingRecord[0] => 'edit'
                        ]
                    ]
                ];
            } else {
                $tsConfig = $this->getTSConfig($pageLayoutController->id);
                $defValConf = $tsConfig['defaultValues.'];
                $defaultValues = isset($defValConf) && is_array($defValConf) ? $defValConf : [];

                $routeConf = [
                    'edit' => [
                        'tt_content' => [
                            $pageLayoutController->id => 'new'
                        ]
                    ],
                    'defVals' => [
                        'tt_content' => $defaultValues
                    ]
                ];
            }

            $routeConf['returnUrl'] = $uriBuilder->buildUriFromModule('/web/layout', [
                'id' => $pageLayoutController->id
            ]);

            $contentEditUri = $uriBuilder->buildUriFromRoute('record_edit', $routeConf);
            HttpUtility::redirect((string) $contentEditUri);
        }
    }

    protected function getTSConfig($pid)
    {
        return BackendUtility::getModTSconfig($pid, 'tx_directcontent')['properties'];
    }
}