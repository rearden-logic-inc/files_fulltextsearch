<?php
declare(strict_types=1);


/**
 * Files_FullTextSearch - Index the content of your files
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Files_FullTextSearch\Provider;


use OCA\Files_FullTextSearch\Exceptions\FileIsNotIndexableException;
use OCA\Files_FullTextSearch\Model\FilesDocument;
use OCA\Files_FullTextSearch\Service\ConfigService;
use OCA\Files_FullTextSearch\Service\FilesService;
use OCA\Files_FullTextSearch\Service\MiscService;
use OCA\Files_FullTextSearch\Service\SearchService;
use OCA\Files_FullTextSearch\Service\SearchTemplateService;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\FullTextSearch\IFullTextSearchPlatform;
use OCP\FullTextSearch\IFullTextSearchProvider;
use OCP\FullTextSearch\Model\IIndex;
use OCP\FullTextSearch\Model\IIndexOptions;
use OCP\FullTextSearch\Model\IndexDocument;
use OCP\FullTextSearch\Model\IRunner;
use OCP\FullTextSearch\Model\ISearchRequest;
use OCP\FullTextSearch\Model\ISearchResult;
use OCP\FullTextSearch\Model\SearchTemplate;


/**
 * Class FilesProvider
 *
 * @package OCA\Files_FullTextSearch\Provider
 */
class FilesProvider implements IFullTextSearchProvider {

	const FILES_PROVIDER_ID = 'files';

	/** @var ConfigService */
	private $configService;

	/** @var FilesService */
	private $filesService;

	/** @var SearchService */
	private $searchService;

	/** @var MiscService */
	private $miscService;

	/** @var SearchTemplateService */
	private $searchTemplateService;

	/** @var IRunner */
	private $runner;

	/** @var IIndexOptions */
	private $indexOptions = [];


	public function __construct(ConfigService $configService, FilesService $filesService,
		SearchService $searchService, SearchTemplateService $searchTemplateService, MiscService $miscService
	) {
		$this->configService = $configService;
		$this->filesService = $filesService;
		$this->searchService = $searchService;
		$this->miscService = $miscService;
		$this->searchTemplateService = $searchTemplateService;
	}


	/**
	 * return unique id of the provider
	 */
	public function getId(): string {
		return self::FILES_PROVIDER_ID;
	}


	/**
	 * return name of the provider
	 */
	public function getName(): string {
		return 'Files';
	}


	/**
	 * @return array
	 */
	public function getConfiguration(): array {
		return $this->configService->getConfig();
	}


	/**
	 * @param IRunner $runner
	 */
	public function setRunner(IRunner $runner) {
		$this->runner = $runner;
	}


	/**
	 * @param IIndexOptions $options
	 */
	public function setIndexOptions(IIndexOptions $options) {
		$this->indexOptions = $options;
	}


	/**
	 * @return SearchTemplate
	 */
	public function getSearchTemplate(): SearchTemplate {
		return $this->searchTemplateService->buildSearchTemplate();
	}


	/**
	 *
	 */
	public function loadProvider() {
	}


	/**
	 * @param string $userId
	 *
	 * @return IndexDocument[]
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 */
	public function generateIndexableDocuments(string $userId): array {
		$this->filesService->setRunner($this->runner);
		$files = $this->filesService->getFilesFromUser($userId, $this->indexOptions);

		return $files;
	}


	/**
	 * @param IndexDocument $document
	 */
	public function fillIndexDocument(IndexDocument $document) {
		/** @var FilesDocument $document */
		$this->updateRunnerInfoArray(
			[
				'info'  => $document->getMimetype(),
				'title' => $document->getPath()
			]
		);

		$this->filesService->generateDocument($document);
	}


	/**
	 * @param IndexDocument $document
	 *
	 * @return bool
	 */
	public function isDocumentUpToDate(IndexDocument $document): bool {
		return $this->filesService->isDocumentUpToDate($document);
	}


	/**
	 * @param IIndex $index
	 *
	 * @return IndexDocument
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @throws FileIsNotIndexableException
	 */
	public function updateDocument(IIndex $index): IndexDocument {
		$document = $this->filesService->updateDocument($index);
		$this->updateRunnerInfo('info', $document->getMimetype());

		return $document;
	}


	/**
	 * @param IFullTextSearchPlatform $platform
	 */
	public function onInitializingIndex(IFullTextSearchPlatform $platform) {
	}


	/**
	 * @param IFullTextSearchPlatform $platform
	 */
	public function onResettingIndex(IFullTextSearchPlatform $platform) {
	}


	/**
	 * not used yet
	 */
	public function unloadProvider() {
	}


	/**
	 * before a search, improve the request
	 *
	 * @param ISearchRequest $request
	 */
	public function improveSearchRequest(ISearchRequest $request) {
		$this->searchService->improveSearchRequest($request);
	}


	/**
	 * after a search, improve results
	 *
	 * @param ISearchResult $searchResult
	 */
	public function improveSearchResult(ISearchResult $searchResult) {
		$this->searchService->improveSearchResult($searchResult);
	}


	/**
	 * @param string $info
	 * @param string $value
	 */
	private function updateRunnerInfo(string $info, string $value) {
		if ($this->runner === null) {
			return;
		}

		$this->runner->setInfo($info, $value);
	}

	/**
	 * @param array $info
	 */
	private function updateRunnerInfoArray(array $info) {
		if ($this->runner === null) {
			return;
		}

		$this->runner->setInfoArray($info);
	}


}
