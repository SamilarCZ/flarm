<?php
	namespace Nette\Security;

	use App\exception\Security\CredentialsException;
	use App\exception\Security\PasswordException;
	use App\exception\Security\TokenException;
	use App\Model\AffiliateModel;
	use App\Model\RoleModel;
	use App\Model\UserModel;
	use Nette\Object;

	class Authenticator extends Object implements IAuthenticator {
		/**
		 * @var AuthenticatorService
		 */
		private $authenticatorService;
		/**
		 * @var UserModel
		 */
		private $userModel;
		/**
		 * @var RoleModel
		 */
		private $roleModel;
		/**
		 * @var AffiliateModel
		 */
		private $affiliateModel;

		public function __construct(AuthenticatorService $authenticatorService, UserModel $userModel, RoleModel $roleModel, AffiliateModel $affiliateModel){
			$this->authenticatorService = $authenticatorService;
			$this->userModel = $userModel;
			$this->roleModel = $roleModel;
			$this->affiliateModel = $affiliateModel;
		}

		/**
		 * @param array $credentials - array('email@example.com', 'password1234')
		 * @return IIdentity
		 * @throws CredentialsException
		 * @throws PasswordException
		 * @throws TokenException
		 */
		public function authenticate(array $credentials) {
			list($email, $password) = $credentials;
			$userExists = $this->userModel->get($this->userModel->where('email=?', $email)->fetch());
			if($userExists !== false){
				$user = $this->userModel->get($userExists->getPrimary())->toArray();
				if(Passwords::verify($password, $user['password']) === false) {
					$this->authenticatorService->logAttempt();
					throw new PasswordException("Bad password.", 0);
				}else {
					$this->authenticatorService->clearLoginAttempt();
					return $this->createIdentity($user);
				}
			}else{
				$this->authenticatorService->logAttempt();
				throw new CredentialsException("Cannot authenticate by given credentials [".(implode(', ', $credentials))."]", 0);
			}
		}

		/**
		 * @param array $user
		 *
		 * @return IIdentity
		 */
		public function createIdentity(array $user) {
			$roleList = [];
			foreach($this->userModel->get($user['id'])->related('role') as $role) {
				$roleList[] = $this->roleModel->get($role['role_id'])->toArray()['name'];
			}
			return new Identity($user['id'], $roleList);
		}
	}
