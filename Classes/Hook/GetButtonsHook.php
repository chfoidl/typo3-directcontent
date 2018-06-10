<?php

namespace Sethorax\DirectContent\Hook;

use Sethorax\DirectContent\Configuration\ExtensionConfiguration;
use Sethorax\DirectContent\Service\PageLanguageService;
use Sethorax\DirectContent\Utility\LanguageUtility;
use TYPO3\CMS\Backend\Template\Components\Buttons\AbstractButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\SplitButton;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

class GetButtonsHook
{
    const TRANSLATION_BUTTON_NAME = '_translate_page';
    const LLPATH = 'LLL:EXT:directcontent/Resources/Private/Language/locallang_be.xlf:';

    public function run(array $params): array
    {
        $returnUrl = GeneralUtility::_GP('returnUrl');

        if (!isset($returnUrl)) {
            return $params['buttons'];
        }

        $pageId = $this->getPageIdFromReturnUrl($returnUrl);
        $page = $this->getPageRepository()->getPage($pageId);

        if (is_array($page) && $page['doktype'] === ExtensionConfiguration::DOKTYPE) {
            $filteredButtons = $this->filterButtons($params['buttons']);
            $languageButton = $this->createTranslationButton($pageId);

            if ($languageButton !== null) {
                $filteredButtons['left'][] = [$languageButton];
            }

            return $filteredButtons;
        }

        return $params['buttons'];
    }

    protected function createTranslationButton(int $pageId): ?AbstractButton
    {
        /** @var PageLanguageService $pageLanguageService */
        $pageLanguageService = GeneralUtility::makeInstance(PageLanguageService::class);
        $languages = $pageLanguageService->getLanguagesForPage($pageId);

        if (count($languages) > 0) {
            $splitButton = new SplitButton();

            $primaryButton = new InputButton();
            $primaryButton
                ->setTitle(LanguageUtility::translate('editdocument.buttonLabel'))
                ->setName(self::TRANSLATION_BUTTON_NAME)
                ->setIcon($this->getIconFactory()->getIcon('actions-localize', Icon::SIZE_SMALL))
                ->setOnClick('window.parent.TYPO3.Notification.notice("' . LanguageUtility::translate('notification.translatePage.title') . '", "' . LanguageUtility::translate('notification.translatePage.description') . '");')
                ->setValue('1');

            $splitButton->addItem($primaryButton, true);

            foreach ($languages as $language) {
                $button = new InputButton();
                $button
                    ->setTitle($language['title'])
                    ->setName(self::TRANSLATION_BUTTON_NAME . '_' . $language['id'])
                    ->setIcon($this->getIconFactory()->getIcon('actions-edit-localize-status-high', Icon::SIZE_SMALL))
                    ->setOnClick($this->createOnClickHandler($language['uri']))
                    ->setValue('1');

                $splitButton->addItem($button);
            }

            return $splitButton;
        } else {
            return null;
        }
    }

    protected function filterButtons(array $buttonGroups): array
    {
        $filteredButtonGroups = [
            'left' => [],
            'right' => $buttonGroups['right']
        ];

        foreach ($buttonGroups['left'] as $button) {
            $button = $button[0];

            if ($button instanceof SplitButton) {
                /** @var InputButton $saveButton */
                $saveButton = $button->getButton()['primary'];
                $saveButton->setShowLabelText(true);

                $filteredButtonGroups['left'][] = [$saveButton];
            }
        }

        return $filteredButtonGroups;
    }

    protected function createOnClickHandler(string $redirectUri): string
    {
        $buttonName = self::TRANSLATION_BUTTON_NAME;

        return <<<JS
(function(window, $) {
    var button = $('button[name="{$buttonName}"]');
    var Icons = window.parent.TYPO3.Icons;
    
    if (button.length > 0) {
        button.prop('disabled', true);
        
        Icons.getIcon('spinner-circle-dark', Icons.sizes.small).done(function(markup) {
            button.find('.t3js-icon').replaceWith(markup); 
        });
    }
    
    window.location.href = '{$redirectUri}';
}(window, $));
JS;

    }

    protected function getPageIdFromReturnUrl(string $returnUrl): ?int
    {
        preg_match_all('/id=([0-9]+)/m', $returnUrl, $matches, PREG_SET_ORDER);

        if (is_array($matches[0]) && MathUtility::canBeInterpretedAsInteger($matches[0][1])) {
            return $matches[0][1];
        }

        return null;
    }

    protected function getPageRepository(): PageRepository
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        return $pageRepository;
    }

    protected function getIconFactory(): IconFactory
    {
        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory;
    }
}