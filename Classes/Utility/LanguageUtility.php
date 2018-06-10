<?php

namespace Sethorax\DirectContent\Utility;

use TYPO3\CMS\Lang\LanguageService;

class LanguageUtility
{
    const LL_BASE_PATH = 'LLL:EXT:directcontent/Resources/Private/Language/';

    public static function translate(string $key, string $xlfName = 'locallang_be'): string
    {
        return self::getLanguageService()->sL(self::LL_BASE_PATH . $xlfName . '.xlf:' . $key);
    }

    public static function getLanguageService(): LanguageService
    {
        /** @var LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];

        return $languageService;
    }
}