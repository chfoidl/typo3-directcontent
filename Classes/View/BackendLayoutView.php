<?php

namespace Sethorax\DirectContent\View;

use Sethorax\DirectContent\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
{
    public function colPosListItemProcFunc(array $parameters)
    {
        $pageId = $this->determinePageId($parameters['table'], $parameters['row']);

        if ($pageId !== false && $this->getDoktype($pageId) === ExtensionConfiguration::DOKTYPE) {
            $parameters['items'] = [[
                'DirectContent',
                $this->getColPos($pageId)
            ]];
        } else {
            parent::colPosListItemProcFunc($parameters);
        }
    }

    protected function getColPos($pageId): int
    {
        $tsConfigColPos = BackendUtility::getPagesTSconfig($pageId)['tx_directcontent.']['defaultValues.']['colPos'];

        if (isset($tsConfigColPos) && is_int($tsConfigColPos)) {
            return $tsConfigColPos;
        } else {
            return ExtensionConfiguration::DEFAULT_COLPOS;
        }
    }

    protected function getDoktype($pageId): int
    {
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        return $dataHandler->pageInfo($pageId, 'doktype');
    }
}