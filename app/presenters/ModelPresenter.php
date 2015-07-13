<?php

	namespace App\Presenters;

	use App\Model\UserModel;
	use FLaRM\DI\FLaRMContainer;
	use Nette\DI\Container;
	use Nette\Framework;

	/**
	 * Database MODEL layer presenter.
	 */
	class ModelPresenter extends BasePresenter {
//		/**
//		 * @var UserModel
//		 */
//		public $userModel;
		/**
		 * @var FLaRMContainer
		 */
		private $FLaRMContainer;

		public function __construct(FLaRMContainer $FLaRMContainer){
			$this->FLaRMContainer = $FLaRMContainer;
		}

		public function renderDemo() {
			echo 'XXXX';
			dump($this->FLaRMContainer);
//			dump($this->getPresenter());
			//dump($this->userModel->getAll());
		}
	}
