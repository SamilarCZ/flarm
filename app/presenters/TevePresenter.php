<?php

	namespace App\Presenters;

	use App\Model\FoldersModel;
	use App\Model\PlaylistModel;
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
		/**
		 * @var FoldersModel
		 */
		private $foldersModel;
		/**
		 * @var PlaylistModel
		 */
		private $playlistModel;

		public function __construct(Container $container, Context $context, FoldersModel $foldersModelFactory, PlaylistModel $playlistModel){
			$this->netteContainer = $container;
			$this->context = $context;
			$this->foldersModel = $foldersModelFactory;
			$this->playlistModel = $playlistModel;
		}

		public function renderIndex(){
			$this->redirect('dashboard');
		}

		public function renderDashboard() {
			$this->template->video1 = 'a';
			dump($this->foldersModel->getId());
		}

	}
