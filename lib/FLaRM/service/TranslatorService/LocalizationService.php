<?php
	namespace App\service;

	use App\Model\CurrencyModel;
	use App\Model\LanguageModel;
	use App\Model\UserModel;
	use Kdyby\Translation\Translator;
	use Nette\Application\UI\Presenter;
	use Nette\DI\Container;
	use Nette\Object;
	use Nette\Utils\Strings;

	class LocalizationService extends Object {
		/**
		 * @var CurrencyModel
		 */
		private $currencyModel;
		/**
		 * @var CustomerService
		 */
		private $customerService;
		/**
		 * @var Translator
		 */
		private $translator;
		/**
		 * @var UserModel
		 */
		private $userModel;
		/**
		 * @var LanguageModel
		 */
		private $languageModel;
		/**
		 * @var Presenter
		 */
		private $presenter;
		/**
		 * @var
		 */
		private $dateFormat;
		private $dateSeparator;


		public function inject(CustomerService $customerService, Translator $translator, LanguageModel $languageModel, Presenter $presenter) {
			$this->customerService = $customerService;
			$this->translator = $translator;
			$this->languageModel = $languageModel;
			$this->presenter = $presenter;
		}

		public function getCurrencyModel() {
			if(!$this->currencyModel) {
				$this->currencyModel = $this->currencyModel->get($this->customerService->getCustomer()['currency_id']);
			}
			return $this->currencyModel;
		}

		public function getUserLanguage() {
			return $this->languageModel->get($this->userModel->get($this->presenter->getUser()->getIdentity()->getId())['language_id']);
		}

		/**
		 * @return string
		 */
		public function getMoneyChar() {
			return $this->getCurrencyModel()['symbol'];
		}

		/**
		 * @return bool
		 */
		public function isMoneyPositionOnRight() {
			return $this->getCurrencyModel()['symbol_position_on_right'];
		}

		/**
		 * Pokud Vrátí 10 znamená že 10 money v databazi je 1 Kč
		 */
		public function getMoneyRatio() {
			return $this->getCurrencyModel()['ratio'];
		}

		/**
		 * @return string
		 */
		public function getCurrencyCode() {
			return Strings::upper($this->getCurrencyModel()['code']);
		}

		/**
		 * @return string
		 */
		public function getDateFormat() {
			$userModel = $this->userModel->get($this->presenter->getUser()->getIdentity()->getId());
			$dateFormat = $userModel['date_format_id'];
			$dateSeparator = $userModel['date_separator_id'];
			if($dateFormat !== null && $dateFormat !== null) {
				return $this->createDateByDateFormatModels($dateSeparator, $dateFormat);
			} else {
				return 'j.n.Y';
			}
		}

		public function getCurrencyListForSelect() {
			$result = [];
			$currencyList = $this->modelWrapper->queryCurrency();
			foreach($currencyList as $currency) {
				$result[$currency->getId()] = $currency->getName();
			}
			return $result;
		}

		/**
		 * @return \Edde3\Model\CurrencyModel[]
		 */
		public function getCurrencyList() {
			return $this->modelWrapper->queryCurrency();
		}

		public function getLanguageListForSelect() {
			$result = [];
			$languageList = $this->modelWrapper->queryLanguage();
			foreach($languageList as $language) {
				$result[$language->getId()] = $language->getName();
			}
			return $result;
		}

		public function getLanguageList() {
			return $this->modelWrapper->queryLanguage();
		}

		public function getDefaultLanguageId() {
			return $this->modelWrapper->queryLanguage()
				->andWhereCodeIsEqual('en')
				->load()
				->getId();
		}

		public function getDefaultCurrencyId() {
			return $this->modelWrapper->queryCurrency()
				->andWhereCodeIsEqual('eur')
				->load()
				->getId();
		}

		public function getCurrencyModelById($id) {
			return $this->modelWrapper->queryCurrency()
				->andWhereIdIsEqual($id)
				->load();
		}

		public function getLanguageModelById($id) {
			return $this->modelWrapper->queryLanguage()
				->andWhereIdIsEqual($id)
				->load();
		}

		/**
		 * @param $code
		 *
		 * @return int
		 */
		public function getLanguageByCode($code) {
			return $this->languageModel->where('code=?', $code)->fetch()->getPrimary();
		}

		/**
		 * @return array
		 */
		public function getDateFormatsToSelect() {
			$dateSeparatorList = $this->modelWrapper->queryDateSeparator();
			$dateFormatList = $this->modelWrapper->queryDateFormat();
			$result = [];
			foreach($dateSeparatorList as $dateSeparator) {
				$separatorArray = [];
				foreach($dateFormatList as $dateFormat) {
					$separatorArray[$dateSeparator->getName().'-'.$dateFormat->getCode()] = $this->createDateByDateNameFromModels($dateSeparator, $dateFormat);
				}
				$result[$this->translator->translate('messages.settings.dateSeparator.'.$dateSeparator->getName())] = $separatorArray;
			}
			return $result;
		}

		private function createDateByDateFormatModels(DateSeparatorModel $dateSeparator, DateFormatModel $dateFormat) {
			return str_replace('-', $dateSeparator->getSymbol(), $dateFormat->getFormat());
		}

		private function createDateByDateNameFromModels(DateSeparatorModel $dateSeparator, DateFormatModel $dateFormat) {
			return str_replace('-', $dateSeparator->getSymbol(), $dateFormat->getName());
		}

		public function getDateSeparatorModelByName($name) {
			try {
				return $this->modelWrapper->queryDateSeparator()
					->andWhereNameIsEqual($name)
					->load();
			} catch(EmptyResultException $e) {
				return false;
			}
		}

		public function getDateFormatModelByCode($code) {
			try {
				return $this->modelWrapper->queryDateFormat()
					->andWhereCodeIsEqual($code)
					->load();
			} catch(EmptyResultException $e) {
				return false;
			}
		}
	}
