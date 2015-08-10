<?php
	namespace App\exception\Security;

	use Nette\Security\User;

	class AclException extends SecurityException {
		/**
		 * @var User
		 */
		private $user;
		/**
		 * @var string
		 */
		private $resource;
		/**
		 * @var string
		 */
		private $privilege;

		public function __construct($aMessage, User $aUser, $aResource, $aPrivilege = null) {
			parent::__construct($aMessage);
			$this->resource = $aResource;
			$this->privilege = $aPrivilege;
		}

		/**
		 * @return User
		 */
		public function getUser() {
			return $this->user;
		}

		/**
		 * @return string
		 */
		public function getPrivilege() {
			return $this->privilege;
		}

		/**
		 * @return string
		 */
		public function getResource() {
			return $this->resource;
		}

	}
