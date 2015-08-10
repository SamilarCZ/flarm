<?php

	namespace App\Presenters;
	use FLaRM\Model\ModelFactoryWrapper;


	/**
	 * DEMO Database MODEL layer presenter.
	 */
	class ModelPresenter extends BasePresenter {
		/**
		 * @var ModelFactoryWrapper
		 */
		public $modelFactoryWrapper;

		public function __construct(ModelFactoryWrapper $modelFactoryWrapper){
			$this->modelFactoryWrapper = $modelFactoryWrapper;
		}

		public function actionDemo() {
			$records = $this->modelFactoryWrapper->modelUserModel()->loadAll();
			foreach ($records as $key => $value) {
				echo $key . ' :: ' . $value->name . '<br />';
			}
		}
	}
