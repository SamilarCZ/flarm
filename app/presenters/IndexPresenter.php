<?php

	namespace App\Presenters;

	use App\Model\UserModel;
	use Nette\Database\Context;
	use Nette\DI\Container;
	use Nette\Framework;
	use Nette\Neon\Exception;
	use Nette\Utils\Finder;


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
			if((Finder::findFiles('UserModel.php')->from('../')->count()) === 0){
				$this->template->hideDemo = true;
				$error = 'Please run this SQL command in your FLaRM database:';
				$errorCommand = 'DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `user` (`id`, `name`, `password`) VALUES
(1,\'admin\',\'admin\');';
			}else{
				$this->template->hideDemo = false;
			}
			$this->template->nette_version = Framework::VERSION;
			$this->template->flarm_version = \FLaRM\Framework::VERSION;
			$this->flashMessage('Connection to database established ! Great work ! Connection created @' . $this->context->getConnection()->getDsn(), 'green');
			if(isset($error)){
				$this->flashMessage($error, 'red');
				$this->flashMessage($errorCommand, 'red');
			}
		}

	}
