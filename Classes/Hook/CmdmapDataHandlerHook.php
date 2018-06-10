<?php

namespace Sethorax\DirectContent\Hook;

use Sethorax\DirectContent\Utility\LanguageUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class CmdmapDataHandlerHook extends AbstractDataHandlerHook
{
    public function processCmdmap_beforeStart(DataHandler $dataHandler)
    {
        $cmdmap = $dataHandler->cmdmap;
        if (empty($cmdmap['tt_content']) || $dataHandler->bypassAccessCheckForRecords) {
            return;
        }

        foreach ($cmdmap['tt_content'] as $id => $incomingFieldArray) {
            foreach ($incomingFieldArray as $command => $value) {
                if (!in_array($command, ['copy', 'move'], true)) {
                    continue;
                }

                $currentRecord = BackendUtility::getRecord('tt_content', $id);

                if (is_array($value)
                    && !empty($value['action'])
                    && 'paste' === $value['action']
                    && isset($value['update']['colPos'])
                ) {
                    $command = 'paste';
                    $pageId = (int)$value['target'];
                    $colPos = (int)$value['update']['colPos'];
                } elseif ($value > 0) {
                    $pageId = (int)$value;
                    $colPos = (int)$currentRecord['colPos'];
                } else {
                    $targetRecord = BackendUtility::getRecord('tt_content', abs($value));
                    $pageId = (int)$targetRecord['pid'];
                    $colPos = (int)$targetRecord['colPos'];
                }
                $currentRecord['pid'] = $pageId;
                $currentRecord['colPos'] = $colPos;

                if ($dataHandler->pageInfo($pageId, 'doktype') === 131 && $this->canSave($currentRecord) === false) {
                    unset($dataHandler->cmdmap['tt_content'][$id]);
                    $dataHandler->log(
                        'tt_content',
                        $id,
                        1,
                        $pageId,
                        1,
                        LanguageUtility::translate('record.invalidCommandMessage'),
                        28,
                        [
                            $command,
                            $currentRecord[$GLOBALS['TCA']['tt_content']['ctrl']['label']]
                        ]
                    );
                }
            }
        }
    }
}