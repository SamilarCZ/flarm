<?php
	namespace Nette\Security;

	use App\Model\AclModel;
	use App\Model\ResourceModel;
	use App\Model\RoleModel;
	use Nette\Caching\Cache;
	use Nette\Caching\IStorage;

	class Authorizator extends Permission {
		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var RoleModel
		 */
		private $roleModel;
		/**
		 * @var ResourceModel
		 */
		private $resourceModel;
		/**
		 * @var AclModel
		 */
		private $aclModel;
		private $acl = null;

		public function __construct(IStorage $aStorage, RoleModel $roleModel, ResourceModel $resourceModel, AclModel $aclModel) {
			$this->cache = new Cache($aStorage, 'Nette.Authorizator');
			$this->roleModel = $roleModel;
			$this->resourceModel = $resourceModel;
			$this->aclModel = $aclModel;
		}

		public function isAllowed($aRole = self::ALL, $aResource = self::ALL, $aPrivilege = self::ALL) {
			$this->build();
			return parent::isAllowed($aRole, $aResource, $aPrivilege);
		}

		public function build() {
			if($this->acl !== null) {
				return;
			}
			if(($this->acl = $this->cache->load($cacheId = 'acl')) === null) {
				$this->acl = array();
				foreach($this->roleModel->getAll() as $role) {
					$this->acl['role'][] = $role['name'];
				}
				foreach($this->resourceModel->getAll() as $resource) {
					$this->acl['resource'][] = $resource['name'];
				}
				foreach($this->aclModel->getAll() as $acl) {
					$access = 'deny';
					if($acl['access'] === 1) {
						$access = 'allow';
					}
					$argz = array($this->roleModel->get($acl['role_id'])['name']);

					if($acl['resource_id']) {
						$argz[] = $this->roleModel->get($acl['resource_id'])['name'];
					}
					if($acl['privilege_id']) {
						$argz[] = $this->roleModel->get($acl['privilege_id'])['name'];
					}
					$this->acl['acl'][] = array(
						$access,
						$argz
					);
				}
				$this->cache->save($cacheId, $this->acl);
			}
			if(isset($this->acl['role'])) {
				foreach($this->acl['role'] as $role) {
					$this->addRole($role);
				}
			}
			if(isset($this->acl['resource'])) {
				foreach($this->acl['resource'] as $resource) {
					$this->addResource($resource);
				}
			}
			if(isset($this->acl['acl'])) {
				foreach($this->acl['acl'] as $acl) {
					call_user_func_array(array(
						$this,
						$acl[0]
					), $acl[1]);
				}
			}
		}
	}
