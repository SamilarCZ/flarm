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
		/**
		 * @var UserModel
		 */
		public $userModel;

		public function __construct(UserModel $userModel){
			$this->userModel = $userModel;
		}

		public function actionDemo() {
			echo 'XXXX';
			dump($this->userModel);
			dump($this->userModel->where('')->fetch()->getPrimary());
//			dump($this->getPresenter());
			//dump($this->userModel->getAll());
		}
	}
