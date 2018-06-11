<?php

namespace Sethorax\DirectContent\Service;

use Sethorax\DirectContent\Utility\Utility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class PageLanguageService implements SingletonInterface
{
    public function getLanguagesForPage(int $pageId)
    {
		$languages = [];
		$overlayTable = Utility::getMajorTYPO3Version() > 8 ? 'pages' : 'pages_language_overlay';

        if ($this->getBackendUser()->check('tables_modify', $overlayTable)) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
            $queryBuilder->getRestrictions()->removeAll();
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(HiddenRestriction::class));
            $statement = $queryBuilder->select('uid', 'title')
                ->from('sys_language')
                ->orderBy('sorting')
                ->execute();
            $availableTranslations = [];
            while ($row = $statement->fetch()) {
                if ($this->getBackendUser()->checkLanguageAccess($row['uid'])) {
                    $availableTranslations[(int)$row['uid']] = $row['title'];
                }
			}
			
            // Then, subtract the languages which are already on the page:
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
            $queryBuilder->getRestrictions()->removeAll();
            $queryBuilder->select('sys_language.uid AS uid', 'sys_language.title AS title')
                ->from('sys_language')
                ->join(
                    'sys_language',
                    $overlayTable,
                    $overlayTable,
                    $queryBuilder->expr()->eq('sys_language.uid', $queryBuilder->quoteIdentifier($overlayTable . '.' . $GLOBALS['TCA'][$overlayTable]['ctrl']['languageField']))
                )
                ->where(
                    $queryBuilder->expr()->eq(
                        $overlayTable . '.deleted',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        $overlayTable . '.' . $GLOBALS['TCA'][$overlayTable]['ctrl']['transOrigPointerField'],
                        $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->gte(
                            $overlayTable . '.t3ver_state',
                            $queryBuilder->createNamedParameter(
                                (string)new VersionState(VersionState::DEFAULT_STATE),
                                \PDO::PARAM_INT
                            )
                        ),
                        $queryBuilder->expr()->eq(
                            $overlayTable . '.t3ver_wsid',
                            $queryBuilder->createNamedParameter($this->getBackendUser()->workspace, \PDO::PARAM_INT)
                        )
                    )
                )
                ->groupBy(
                    $overlayTable . '.' . $GLOBALS['TCA'][$overlayTable]['ctrl']['languageField'],
                    'sys_language.uid',
                    'sys_language.pid',
                    'sys_language.tstamp',
                    'sys_language.hidden',
                    'sys_language.title',
                    'sys_language.language_isocode',
                    'sys_language.static_lang_isocode',
                    'sys_language.flag',
                    'sys_language.sorting'
                )
                ->orderBy('sys_language.sorting');
            if (!$this->getBackendUser()->isAdmin()) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        'sys_language.hidden',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    )
                );
            }
            $statement = $queryBuilder->execute();
            while ($row = $statement->fetch()) {
                unset($availableTranslations[(int)$row['uid']]);
            }
            // Remove disallowed languages
            if (!empty($availableTranslations)
                && !$this->getBackendUser()->isAdmin()
                && $this->getBackendUser()->groupData['allowed_languages'] !== ''
            ) {
                $allowed_languages = array_flip(explode(',', $this->getBackendUser()->groupData['allowed_languages']));
                if (!empty($allowed_languages)) {
                    foreach ($availableTranslations as $key => $value) {
                        if (!isset($allowed_languages[$key]) && $key != 0) {
                            unset($availableTranslations[$key]);
                        }
                    }
                }
            }
            // Remove disabled languages
            $modSharedTSconfig = BackendUtility::getModTSconfig($pageId, 'mod.SHARED');
            $disableLanguages = isset($modSharedTSconfig['properties']['disableLanguages'])
                ? GeneralUtility::trimExplode(',', $modSharedTSconfig['properties']['disableLanguages'], true)
                : [];
            if (!empty($availableTranslations) && !empty($disableLanguages)) {
                foreach ($disableLanguages as $language) {
                    if ($language != 0 && isset($availableTranslations[$language])) {
                        unset($availableTranslations[$language]);
                    }
                }
            }

            if (!empty($availableTranslations)) {
                foreach ($availableTranslations as $languageUid => $languageTitle) {
                    $parameters = [
                        'justLocalized' => 'pages:' . $pageId . ':' . $languageUid,
                        'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
                    ];
                    $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                    $redirectUrl = (string)$uriBuilder->buildUriFromRoute('record_edit', $parameters);
                    $targetUrl = BackendUtility::getLinkToDataHandlerAction(
                        '&cmd[pages][' . $pageId . '][localize]=' . $languageUid,
                        $redirectUrl
                    );

                    $languages[] = [
                        'id' => $languageUid,
                        'title' => $languageTitle,
                        'uri' => $targetUrl
                    ];
                }
            }
        }

        return $languages;
    }

    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
