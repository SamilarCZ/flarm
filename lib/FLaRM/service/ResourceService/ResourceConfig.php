<?php
	namespace App\service\ResourceService;

	use NetteUtils\DI\Configurator;

	class ResourceConfig extends Configurator {
		private $__propertyList = [];
		private $__changeList = [];
		protected $parameters = [];

		public function __construct(array $propertyList = []) {
			parent::__construct();
			$this->setPropertyValuesBypass($propertyList);
//			$this->configurator = $configurator;
		}

		protected function config(array $config) {
			if (!isset($this->parameters['resource-service'])) {
				return [];
			}
			return $this->parameters['resource-service'];
		}

		private function getTempDirLocation(){
			if(!isset($this->parameters['tempDir'])){
				$this->setTempDirectory(__DIR__ . '/../../../temp');
			}
			return $this->parameters['tempDir'];
		}

		/**
		 * @return string[]
		 */
		public function getPath() {
			if(!isset($this->parameters['tempDir'])){
				$this->setTempDirectory(__DIR__ . '/../../../temp');
			}
			return $this->parameters;
		}

		/**
		 * @return string
		 */
		public function getResourceStore() {
			return $this->getOrDefault('resource-store', $this->getTempDirLocation() . '/resource-store', true);
		}

		/**
		 * @param string $property
		 * @param mixed|null $default
		 * @param bool $push
		 *
		 * @return mixed|null
		 */
		public function getOrDefault($property, $default = null, $push = false) {
			if(isset($this->__propertyList[$property]) || array_key_exists($property, $this->__propertyList)) {
				if(($get = $this->parameters[$property]) === null) {
					return $default;
				}
				return $get;
			}
			if($push === true) {
				$this->addParameters([$property => $default]);
			}
			return $default;
		}

		/**
		 * nastaví kontejneru hodnoty; obejde veškeré kontrolní mechanismy - přistupuje přímo k vlastnostem kontejneru
		 *
		 * @param array $propertyValues
		 *
		 * @return $this
		 */
		public function setPropertyValuesBypass(array $propertyValues) {
			$this->__propertyList = $propertyValues;
			return $this;
		}

	}
