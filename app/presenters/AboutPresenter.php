<?php

	namespace App\Presenters;
	use FLaRM\Model\ModelFactoryWrapper;


	class AboutPresenter extends BasePresenter {
		/**
		 * @var ModelFactoryWrapper
		 */
		public $modelFactoryWrapper;

		public function __construct(ModelFactoryWrapper $modelFactoryWrapper){
			$this->modelFactoryWrapper = $modelFactoryWrapper;
		}

		public function actionUs() {

		}
	}
