<?php

	namespace App\Presenters;

	use App\Model\UserModel;
	use Nette\Database\Context;
	use Nette\DI\Container;
	use Nette\Framework;
	use Nette\Neon\Exception;


	/**
	 * Index presenter.
	 */
	class IndexPresenter extends BasePresenter {
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

		public function renderIndex() {
			$this->template->nette_version = Framework::VERSION;
			$this->template->flarm_version = \FLaRM\Framework::VERSION;
			$this->flashMessage('Connection to database established ! Great work ! Connection created @' . $this->context->getConnection()->getDsn(), 'green');
		}

	}
