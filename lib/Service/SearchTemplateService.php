<?php

namespace OCA\Files_FullTextSearch\Service;

use OCP\FullTextSearch\Model\SearchOption;
use OCP\FullTextSearch\Model\SearchTemplate;
use OCP\IL10N;

class SearchTemplateService {

    /** @var IL10N */
    private $l10n;

    /** @var ConfigService */
    private $configService;

    /** @var ExtensionService */
    private $extensionService;

    /**
     * SearchTemplateService constructor.
     * @param IL10N $l10n
     * @param ConfigService $configService
     * @param ExtensionService $extensionService
     */
    public function __construct(IL10N $l10n, ConfigService $configService, ExtensionService $extensionService) {
        $this->l10n = $l10n;
        $this->configService = $configService;
        $this->extensionService = $extensionService;
    }

    public function buildSearchTemplate() {
        $template = new SearchTemplate('icon-fts-files', 'fulltextsearch');

        $this->extensionService->searchTemplateRequest($template);

        if (empty($template->getPanelOptions()) && empty($template->getNavigationOptions())) {

            $this->addCurrentDirectoryOption($template);
            $this->addLocalFilesOption($template);
            $this->addExternalFilesOption($template);
            $this->addGroupFoldersOption($template);
            $this->addExtensionOption($template);

        }

        return $template;
    }

    public function addCurrentDirectoryOption(SearchTemplate $template) {
        $template->addPanelOption(
            new SearchOption(
                'files_within_dir', $this->l10n->t('Within current directory'),
                SearchOption::CHECKBOX
            )
        );
    }

    public function addLocalFilesOption(SearchTemplate $template) {
        $template->addPanelOption(
            new SearchOption(
                'files_local', $this->l10n->t('Within local files'),
                SearchOption::CHECKBOX
            )
        );
        $template->addNavigationOption(
            new SearchOption(
                'files_local', $this->l10n->t('Local files'),
                SearchOption::CHECKBOX
            )
        );
    }

    public function addExternalFilesOption(SearchTemplate $template) {
        if ($this->configService->getAppValue(ConfigService::FILES_EXTERNAL) === '1') {
            $template->addPanelOption(
                new SearchOption(
                    'files_external', $this->l10n->t('Within external files'),
                    SearchOption::CHECKBOX
                )
            );
            $template->addNavigationOption(
                new SearchOption(
                    'files_external', $this->l10n->t('External files'), SearchOption::CHECKBOX
                )
            );
        }
    }

    public function addGroupFoldersOption(SearchTemplate $template) {
        if ($this->configService->getAppValue(ConfigService::FILES_GROUP_FOLDERS) === '1') {
            $template->addPanelOption(
                new SearchOption(
                    'files_group_folders', $this->l10n->t('Within group folders'),
                    SearchOption::CHECKBOX
                )
            );
            $template->addNavigationOption(
                new SearchOption(
                    'files_group_folders', $this->l10n->t('Group folders'),
                    SearchOption::CHECKBOX
                )
            );
        }
    }

    public function addExtensionOption(SearchTemplate $template) {
        $template->addPanelOption(
            new SearchOption(
                'files_extension', $this->l10n->t('Filter by extension'), SearchOption::INPUT,
                SearchOption::INPUT_SMALL, 'txt'
            )
        );
        $template->addNavigationOption(
            new SearchOption(
                'files_extension', $this->l10n->t('Extension'), SearchOption::INPUT,
                SearchOption::INPUT_SMALL, 'txt'
            )
        );
    }

}