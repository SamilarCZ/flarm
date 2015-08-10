<?php
	namespace App\service;

	use App\exception\Model\EmptyResultException;
	use App\exception\Translations\NoTranslationFound;
	use App\Model\LanguageModel;
	use App\Model\TranslatedtextModel;
	use Kdyby\Translation\Translator;
	use Latte\Loaders\StringLoader;
	use Latte\Object;
	use Nette\Bridges\ApplicationLatte\Template;
	use Nette\Utils\Finder;

	class TranslatorService extends Object {
		/**
		 * @var Translator
		 */
		private $translator;
		/**
		 * @var TranslatorSettings
		 */
		private $translatorSettings;
		/**
		 * @var LanguageModel
		 */
		private $languageModel;
		/**
		 * @var LocalizationService
		 */
		private $localizationService;
		/**
		 * @var TranslatedtextModel
		 */
		private $translatedTextModel;

		private $fileTypes = ['*.latte'];

		public function __construct(TranslatorSettings $translatorSettings, Translator $translator, LocalizationService $localizationService, TranslatedtextModel $translatedtextModel, LanguageModel $languageModel) {
			$this->translatorSettings = $translatorSettings;
			$this->translator = $translator;
			$this->localizationService = $localizationService;
			$this->translatedTextModel = $translatedtextModel;
			$this->languageModel = $languageModel;
		}

		public function getTranslatedText($key, $languageCode = null) {
			try {
				if ($languageCode === null) {
					$languageModel = $this->loadLanguageModel();
				} else {
					$languageModel = $this->languageModel->where('code=?', $languageCode)->fetch();
				}
				$translatedTextModel = $this->translatedTextModel->where('language_id=? AND key=?' , [$languageModel->getPrimary(), $key])->fetch();
				return $translatedTextModel['text'];
			} catch (EmptyResultException $e) {
				throw new NoTranslationFound(sprintf('Translation with key [%s] do not exist in language [%s]', $key, $this->languageModel['name']));
			}
		}

		private function loadLanguageModel($languageModel = null) {
			if ($languageModel === null) {
				$languageModel = $this->languageModel->where('code=? OR id=?' , [$this->translator->getLocale(), $this->translator->getLocale()])->fetch();
			}else{
				$languageModel = $this->languageModel->where('code=? OR id=?' , [$languageModel, $languageModel])->fetch();
			}
			return $languageModel;
		}

		/**
		 * @doc Nastaví sablonovacimu systemu obsah podle jazyku, pokud neni jazyk definovany tak se pouzije
		 * @param $key
		 * @param Template $template
		 * @param null $languageCode
		 * @throws NoTranslationFound
		 */
		public function fillTemplate($key, Template $template, $languageCode = null) {
			$template->getLatte()->setLoader(new StringLoader());
			$template->setFile($this->getTranslatedText($key, $languageCode));
		}

		public function importTranslations($zipFileName = null) {
			if ($zipFileName !== null) {
				$this->importZip($zipFileName);
			}
			try {
				foreach ($this->getTranslationsToImport() as $file) {
					$pathInfo = pathinfo($file);
					list($key, $languageCode) = explode('.', $pathInfo['filename']);
					$newTranslatedTextArray = [];
					$newTranslatedTextArray['language_id'] = $this->localizationService->getLanguageByCode($languageCode);
					$newTranslatedTextArray['key'] = $key;
					$newTranslatedTextArray['text'] = file_get_contents($file);
					$this->translatedTextModel->insert($newTranslatedTextArray);
				}
			} catch (\Exception $e) {
				throw $e;
			}
		}

		/**
		 * @return string
		 */
		public function exportTranslations() {
			if (count(glob($this->translatorSettings->getPath() . '/*.neon')) == 0) {
				foreach (new \DirectoryIterator($this->translatorSettings->getDefaultPath()) as $file) {
					if ($file->getExtension() === 'neon') {
						copy($file->getRealPath(), $this->translatorSettings->getPath() . '/' . $file->getFilename());
					}
				}
			}
			foreach ($this->getAllTranslationModels() as $translationModel) {
				$filename = $translationModel->getPrimary() . '.' . $this->languageModel->where('code=?', $translationModel['language_id'])->fetch()['code'] . '.latte';
				$filePath = $this->translatorSettings->getTextPath() . '/' . $filename;
				file_put_contents($filePath, $translationModel['text']);
			}
			return $this->zipDirectoryToDirectory($this->translatorSettings->getPath(), $this->translatorSettings->getHistoryExportPath(), $this->createBackupFilename());
		}

		protected function getTranslationsToImport() {
			return Finder::findFiles($this->fileTypes)->from($this->translatorSettings->getPath());
		}

		protected function truncateTranslations() {
			foreach ($this->translatedTextModel->getAll() as $translatedText) {
				$this->translatedTextModel->get($translatedText->getPrimary())->delete();
			}
		}

		protected function getAllTranslationModels() {
			return $this->translatedTextModel->getAll();
		}

		private function importZip($zipFileName) {
			$newZipFilePath = $this->translatorSettings->getHistoryImportPath() . '/' . $this->createBackupFilename();
			rename($zipFileName, $newZipFilePath);
			$this->exportZipTo($newZipFilePath, $this->translatorSettings->getPath());
		}

		/**
		 * @param $zipPath string Cesta k zip souboru
		 * @param $to string Slozka kam se to extrahuje
		 */
		private function exportZipTo($zipPath, $to) {
			$zip = new \ZipArchive();
			$res = $zip->open($zipPath);
			if ($res) {
				$zip->extractTo($to);
			} else {
				throw new \RuntimeException('Zip archive to import not exist');
			}
		}

		/**
		 * @param $directory string Složka k zazipování
		 * @param $toDirectory string Kam to zazipovat
		 *
		 * @param $zipFilename string Název vytvořeného archívu
		 *
		 * @return string
		 */
		private function zipDirectoryToDirectory($directory, $toDirectory, $zipFilename) {
			$dir = realpath($directory);
			$zipFilePath = $toDirectory . '/' . $zipFilename;
			$zip = new \ZipArchive();
			$resource = $zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
			if ($resource === true) {
				$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY);
				foreach ($files as $name => $file) {
					if (!$file->isDir()) {
						$filePath = $file->getRealPath();
						if (in_array($file->getExtension(), ['neon', 'latte'])) {
							$relativePath = substr($filePath, strlen($dir) + 1);
							$zip->addFile($filePath, $relativePath);
						}
					}
				}
				$zip->close();
			} else {
				throw new \RuntimeException('Failed to create zip archive');
			}
			$archiveRealPath = realpath($zipFilePath);
			if (!$archiveRealPath) {
				throw new \RuntimeException('Zip archive do not exist, is allowed to write ?');
			}
			return $archiveRealPath;
		}

		private function createBackupFilename() {
			return 'translator_' . date('Y_m_d_H_i') . '.zip';
		}
	}
