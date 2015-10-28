<?php

	namespace App\Presenters;
	use App\FrontModule\Forms\RegisterForm;
	use App\FrontModule\Forms\RegisterStep2Form;
	use FLaRM\Model\ModelFactoryWrapper;
	use Nette\Application\UI\Form;
	use Nette\Utils\DateTime;
	use Nette\Utils\Random;
	use Nette\Utils\Strings;


	class SignPresenter extends BasePresenter {
		/**
		 * @var ModelFactoryWrapper
		 */
		public $modelFactoryWrapper;

		public function __construct(ModelFactoryWrapper $modelFactoryWrapper){
			$this->modelFactoryWrapper = $modelFactoryWrapper;
		}

		public function createComponentRegisterForm() {
			$form = new RegisterForm($this);
			$form->onSubmit[] = callback($this, 'processRegisterForm');
			return $form;
		}

		public function createComponentRegisterStep2Form() {
			$form = new RegisterStep2Form($this);
			$form->onSubmit[] = callback($this, 'processRegisterStep2Form');
			return $form;
		}

		public function actionRegister() {

		}

		public function actionLogin() {

		}

		public function actionTakeALook() {

		}

		public function actionDirectToken($token) {
			if(!empty($token)) {
				if($userModel = $this->modelFactoryWrapper->modelUserModel()->getModelQuery()->andWhereDirectLoginTokenIsEqual($token)->load()) {
					$this->flashMessage('Great ! You made it to the second step of register ;)', 'success');

				} else {
					$this->flashMessage('TOKEN MISMATCH ! We can\'t find your token in our database !', 'danger');
				}
			} else {
				$this->flashMessage('TOKEN MISSING !', 'danger');
			}
		}

		public function processRegisterForm(Form $form) {
			if($form->isValid()){
				$values = $form->getValues();
				$emailExists = $this->modelFactoryWrapper->modelUserModel()->getModelQuery()->andWhereEmailIsEqual($values->email)->load();
				if(empty($emailExists)) {
					$userModel = $this->modelFactoryWrapper->modelUserModel()->getModel();
					$userModel->setEmail($values->email);
					$userModel->setPassword(Random::generate(6));
					$userModel->setDirectLoginToken(Strings::webalize(Random::generate(25)));
					$userModel->setCreated(new DateTime());
					$saveEmail = $userModel->save();
					if($saveEmail) {
						$this->flashMessage('Registration done ! Check your e-mail box for next instructions. :)', 'success');
					} else {
						$this->flashMessage('Registration CRUSHED! Some problem occurred. Try again please', 'danger');
					}
				} else {
					$this->flashMessage('This e-mail is already in use. Try another one or contact support please.', 'warning');
				}
				$this->redirect('this');
			} else {
				if($errors = $form->getErrors()) {
					foreach($errors as $error) {
						$this->flashMessage($error, 'warning');
					}
				}
			}
		}

		public function processRegisterStep2Form(Form $form) {

		}
	}
