<?php
	namespace Nette\Security;

	use App\exception\Model\EmptyResultException;
	use App\Model\AuthenticationAttemptModel;
	use App\Model\UserModel;
	use Nette\Http\Request;
	use Nette\Object;
	use Nette\Utils\DateTime;

	class AuthenticatorService extends Object {
		/**
		 * @var Request
		 */
		private $request;
		/**
		 * @var AuthenticationAttemptModel
		 */
		private $authenticationAttemptModel;
		/**
		 * @var UserModel
		 */
		private $userModel;

		public function __construct(Request $request, AuthenticationAttemptModel $authenticationAttemptModel, UserModel $userModel) {
			$this->request = $request;
			$this->authenticationAttemptModel = $authenticationAttemptModel;
			$this->userModel = $userModel;
		}

		public function logAttempt() {
			$attemptData = [];
			$existingRow = $this->authenticationAttemptModel->where('ip=?', $this->request->getRemoteAddress())->fetch();
			$attemptData['ip'] = $this->request->getRemoteAddress();
			$attemptData['stamp'] = new DateTime('NOW');
			$attemptData['count'] = $existingRow['count'] + 1 ;
			if($existingRow !== false){
				$attemptData['id'] = $existingRow->getPrimary();
				$this->authenticationAttemptModel->update($attemptData);
			} else{
				$this->authenticationAttemptModel->insert($attemptData);
			}
		}

		public function clearLoginAttempt() {
			if($this->authenticationAttemptModel->where('ip=?', $this->request->getRemoteAddress())->fetch() !== false){
				$this->authenticationAttemptModel->where('ip=?', $this->request->getRemoteAddress())->delete();
			}
		}

		/**
		 * vrátí true, pokud byl přesažen počet pokusů o přihlášení)
		 *
		 * @param int $limit
		 *
		 * @return bool
		 */
		public function isAttemptLimit($limit = 3) {
			try{
				return $this->authenticationAttemptModel->where('ip=?', $this->request->getRemoteAddress())->fetch()['count'] > $limit;
			} catch (EmptyResultException $e){}
			return false;
		}

		/**
		 * vrací true pokud se jedna o prvni prihlaseni po registraci
		 *
		 * @param $user_id
		 *
		 * @return bool
		 */
		public function isFirstAuth($user_id){
			if($userModel = $this->userModel->where('id=?', $user_id)->fetch()){
				if(isset($userModel['direct_login_token'])){
					return true;
				}else{
					return false;
				}
			}
		}
	}
