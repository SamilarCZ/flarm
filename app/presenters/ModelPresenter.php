<?php

	namespace App\Presenters;

	use App\Model\UserModel;

	/**
	 * DEMO Database MODEL layer presenter.
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
			$records = $this->userModel->getAll();
			foreach ($records as $key => $value) {
				echo $key . ' :: ' . $value->name . '<br />';
				echo $key . ' :: ' . $value['name'] . '<br />';
			}
		}
	}
