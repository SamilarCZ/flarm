<?php

	namespace App\Presenters;

	use App\Model\DirModel;
	use App\Model\FileModel;
	use App\Model\PlaylistModel;
	use Nette\Bridges\DatabaseDI\DatabaseExtension;
	use Nette\Database\Context;
	use Nette\Database\Drivers\MySqlDriver;
	use Nette\DI\Container;
	use Nette\Utils\FileSystem;
	use Nette\Utils\Finder;
	use Nette\Utils\Strings;
	use Tracy\Debugger;


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
		 * @var DirModel
		 */
		private $dirModel;
		/**
		 * @var FileModel
		 */
		private $fileModel;
		/**
		 * @var PlaylistModel
		 */
		private $playlistModel;

		public function __construct(Container $container, Context $context, DirModel $dirModel, PlaylistModel $playlistModel, FileModel $fileModel){
			$this->netteContainer = $container;
			$this->context = $context;
			$this->dirModel = $dirModel;
			$this->playlistModel = $playlistModel;
			$this->fileModel = $fileModel;
		}

		public function renderIndex(){
			$this->redirect('dashboard');
		}

		public function renderDashboard() {
			$this->template->video1 = 'a';
			if(is_array($baseFolders = $this->netteContainer->parameters['teve']['base-folders'])) {
				$this->template->files = $this->getFiles($baseFolders);
//				foreach ($files as $key => $value) {
//					/**
//					 * @var \SplFileInfo $value
//					 */
//					if(is_array($value)){
//						foreach($value as $k => $v){
//							/**
//							 * @var \SplFileInfo $v
//							 */
//							if(!is_dir($v->getBasename())){
//								if(in_array($v->getExtension(),['avi', 'mpeg', 'mp4', 'mov', 'mkv', '3gp', 'mov', 'flv'])) {
////									$newDir['path'] = Strings::fixEncoding($v->getPath());
////									if ($insertedDir = $this->dirModel->where('path=?', Strings::fixEncoding($v->getPath()))->fetch() === false) $insertDir = $this->dirModel->insert($newDir);
////									$newFile['path'] = $v->getRealPath();
////									$newFile['dir_id'] = ((isset($insertDir)) ? $insertDir->getPrimary() : $insertedDir['id']);
////									$this->fileModel->insert($newFile);
//								}
//							}
//						}
//					}
//				}
			}
		}

		private function getFiles($baseFolders){
			$files = [];
			$maskMovies = ['*', '*.avi', '*.mp4', '*.mpeg', '*.mpg', '*.mkv', '*.mov', '*.flv', '*.3gp'];
			foreach (Finder::find($maskMovies)->in($baseFolders) as $key => $directory) {
				if(is_dir($directory)) {
//					$newDir['path'] = htmlspecialchars($directory);
//					if($this->dirModel->where('path=?', $newDir['path'])->fetch() === false AND $newDir['path'] !== ''){
//						$this->dirModel->insert($newDir);
//					}
					$files[] = $this->getFiles($directory);
				}else{
//					$newFile['path'] = htmlspecialchars($directory);
//					$newFile['dir_id'] = $this->dirModel->where('path=?', htmlspecialchars(pathinfo($directory)['dirname']))->fetch()['id'];
//					$this->fileModel->insert($newFile);
					$files[] = $directory;
				}
			}
			return $files;
		}

	}
