<?php

namespace Sethorax\DirectContent\Hook;

use Sethorax\DirectContent\Configuration\ExtensionConfiguration;
use Sethorax\DirectContent\Utility\LanguageUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapDataHandlerHook extends AbstractDataHandlerHook
{
    public function processDatamap_beforeStart(DataHandler $dataHandler)
    {
        $datamap = $dataHandler->datamap;
        if (empty($datamap['tt_content']) || $dataHandler->bypassAccessCheckForRecords) {
            return;
        }

        foreach ($datamap['tt_content'] as $id => $incomingFieldArray) {
            $incomingFieldArray['uid'] = $id;
            if (MathUtility::canBeInterpretedAsInteger($id)) {
                $incomingFieldArray = array_merge(BackendUtility::getRecord('tt_content', $id), $incomingFieldArray);
            }

            $pageId = (int)$incomingFieldArray['pid'];

            if ($pageId < 0) {
                $previousRecord = BackendUtility::getRecord('tt_content', abs($pageId), 'pid');
                $pageId = (int)$previousRecord['pid'];
                $incomingFieldArray['pid'] = $pageId;
            }

            // Check if page is of type directcontent
            if ($dataHandler->pageInfo($pageId, 'doktype') === ExtensionConfiguration::DOKTYPE && $this->canSave($incomingFieldArray) === false) {
                // Unset content element and therefore deny the creation of a new content element for this page
                unset($dataHandler->datamap['tt_content'][$id]);

                // Display error message
                $dataHandler->log(
                    'tt_content',
                    $id,
                    1,
                    $pageId,
                    1,
                    LanguageUtility::translate('record.savingDeniedMessage'),
                    27,
                    [
                        $incomingFieldArray[$GLOBALS['TCA']['tt_content']['ctrl']['label']]
                    ]
                );
            }
        }
    }
}