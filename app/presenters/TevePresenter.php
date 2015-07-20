<?php

	namespace App\Presenters;

	use Nette\Database\Context;
	use Nette\DI\Container;


	/**
	 * Teve presenter. The multimedial center by Samilar
	 */
	class TevePresenter extends BasePresenter {
		/**
		 * @var Container
		 */
		private $netteContainer;
		/**
		 * @var Context
		 */
		private $context;

		public function __construct(Container $container, Context $context){
			$this->netteContainer = $container;
			$this->context = $context;
		}

		public function renderDashboard() {

		}

	}
