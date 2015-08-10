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
			/**
			 * EXAMPLE 1
			 */
			/*
			$records = $this->modelFactoryWrapper->modelUserModel()->loadAll();
			foreach ($records as $key => $value) {
				echo $key . ' :: ' . $value->name . '<br />';
			}
			*/

			/**
			 * EXAMPLE 2
			 */
			/*
			// load empty object model
			$newModel = $this->modelFactoryWrapper->modelUserModel()->getModel();
			// now set the values you want ( FLaRM will help you don't worry ;) )
			$newModel->setLogin('xxx');
			$newModel->setDateRegister(new \DateTime());
			$newModel->setName('xxx');
			$newModel->setSurname('xxx');
			// $newModel->setId(2);
			// now you can save all the data you set to the model object
			$newModel->save();
			*/
			/**
			 * EXAMPLE 3
			 */
			/*
			// first of all you must find what you want to delete. So get the object query !
			$findObject = $this->modelFactoryWrapper->modelUserModel()->getModelQuery();
			// use Where methods to specify your query
			$findObject->andWhereIdIsEqual(1)->orWhereIdIsEqual(2);
			// And now tell FLaRM that you want to DELETE it ! ;)
			$findObject->deĺete();
			*/
		}
	}
