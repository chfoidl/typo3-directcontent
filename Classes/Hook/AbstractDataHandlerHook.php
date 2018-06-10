<?php

namespace Sethorax\DirectContent\Hook;

use Sethorax\DirectContent\Utility\ContentUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractDataHandlerHook
{
    /**
     * @param array $record
     * @return bool
     */
    protected function canSave(array $record)
    {
        $languageField = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];
        $language = (array)$record[$languageField];

        $records = ContentUtility::fetchRecordsForPage($record['pid'], $language[0]);

        return count($records) < 1 || $record['uid'] === $records[0];
    }

    protected function fetchRecords(array $record): array
    {
        $languageField = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];
        $language = (array)$record[$languageField];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);

        return $queryBuilder->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($record['pid'], \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    $languageField,
                    $queryBuilder->createNamedParameter($language[0], \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}