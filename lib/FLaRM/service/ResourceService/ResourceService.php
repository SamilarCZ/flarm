<?php
	namespace App\service\ResourceService;

	use App\Model\UserModel;
	use Nette\Caching\Cache;
	use Nette\Caching\IStorage;
	use Nette\Configurator;
	use Nette\Http\FileUpload;
	use Nette\Http\Url;
	use Nette\Object;
	use Nette\Utils\FileSystem;
	use Nette\Utils\FileSystemUtils;
	use Nette\Utils\Finder;
	use Nette\Utils\Random;
	use Nette\Utils\Strings;
	use NetteUtils\DI\Config\CommonConfig;

	class ResourceService extends Object {
		private static $resources = '*';

		private $resourceConfig;
		/**
		 * @var CommonConfig
		 */
		private $commonConfig;

		/**
		 * @var Cache
		 */
		private $cache;
		/**
		 * @var UserModel
		 */
		private $userModel;
		/**
		 * @var string[]
		 */
		private $index = [];
		/**
		 * pole vyžadovaných zdrojů v rámci tohoto requestu - pokud cacheovat, tak VELMI opatrně
		 *
		 * @var string[]
		 */
		private $request = [];
		private $built = false;

		public function __construct(IStorage $storage, UserModel $userModel, CommonConfig $commonConfig) {
			$this->resourceConfig = new ResourceConfig();
			$this->cache = new Cache($storage, 'Nette.ResourceService');
			$this->userModel = $userModel;
			$this->commonConfig = $commonConfig;
		}

		public function rebuild() {
			$this->built = false;
			$this->cache->save('index', null);
			$this->build();
			return $this;
		}

		public function build() {
			if($this->built === true || ($this->index = $this->cache->load($cacheId = 'index')) !== null) {
				return $this;
			}
			$this->built = true;
			if(empty($this->resourceConfig->getPath())) {
				throw new \Exception('Requested resource service, but no search path was specified. Please update config - set bullet path list to section resource.');
			}
			FileSystem::createDir($this->resourceConfig->getResourceStore(), 0775);
			$this->index = [];
			$statics = str_replace('\\', '/', $this->resourceConfig->getPath()['tempDir']);
			foreach(Finder::findFiles(self::$resources)->filter(function (\SplFileInfo $file) use ($statics) {return strpos(FileSystemUtils::normalizePath($file->getPathname()), $statics) === false;})->from($this->resourceConfig->getPath()['tempDir']) as $path => $info) {
				$this->index[] = FileSystemUtils::normalizePath($path);
			}
			$this->cache->save($cacheId, $this->index);
			return $this;
		}

		public function find($resource, $need = true) {
			$this->build();
			$resource = preg_quote($resource, '~');
			$resources = preg_grep("~{$resource}$~i", $this->index);
			switch(count($resources)) {
				case 0:
					if($need === true) {
						throw new  \Exception(sprintf("Cannot locate requested resource '%s'. Did you configure ResourceService in config - 'resource' section? (single path or bullet list of paths)", $resource));
					}
					return null;
				case 1:
					$resource = reset($resources);
					break;
				default:
					throw new \Exception(sprintf("Multiple resource items found for requested resource '%s': [%s].", $resource, implode(', ', $resources)));
			}
			return $resource;
		}

		protected function getStorePath() {
			return $this->resourceConfig->getResourceStore().'/';//.$this->userModel->get();
		}

		public function store(FileUpload $fileUpload) {
			$path = $this->getStorePath();
			if($fileUpload->isImage()) {
				$path .= '/image';
			}
			FileSystem::createDir($path, 0775);
			$fileUpload->move($file = ($path.'/'.microtime().'-'.$fileUpload->getSanitizedName()));
			if($fileUpload->isImage()) {
				$image = $fileUpload->toImage();
				$image->resize(1024, 1024);
				$image->save($file);
			}
			$this->rebuild();
			return $this->link($fileUpload->getSanitizedName());
		}

		public function listImageStore() {
			$list = [];
			try {
				/** @var $file \SplFileInfo */
				foreach(Finder::findFiles(self::$resources)
							->from($this->getStorePath().'/image') as $info => $file) {
					$link = $this->link(FileSystemUtils::normalizePath($info));
					$list[] = [
						'thumb' => $link,
						'image' => $link,
						"title" => $file->getFilename(),
					];
				}
			} catch(\Exception $e) {
			}
			return $list;
		}

		public function listFileStore() {
			$list = [];
			try {
				/** @var $file \SplFileInfo */
				foreach(Finder::findFiles('*')
							->from($this->getStorePath()) as $info => $file) {
					$list[] = [
						'title' => $file->getFilename(),
						'name' => $file->getFilename(),
						"link" => $this->link(FileSystemUtils::normalizePath($info)),
						'size' => '-',
					];
				}
			} catch(\Exception $e) {
			}
			return $list;
		}

		/**
		 * zažádá o zdroj, který pak lze zkompilovat do balíčku
		 *
		 * @param string $aResource
		 * @param string $aType
		 *
		 * @return $this
		 */
		public function request($aResource, $aType) {
			$this->request[$aType][] = $this->find($aResource);
			return $this;
		}

		public function requestList(array $aResourceList, $aType) {
			foreach($aResourceList as $resource) {
				$this->request($resource, $aType);
			}
			return $this;
		}

		/**
		 * zajistí publikování daného zdroje do veřejně dostupné oblasti (pod hashem) a vrátí jeho absolutní cestu na fileystému
		 *
		 * @param string $resource
		 *
		 * @throws \Exception
		 *
		 * @return string
		 */
		public function resource($resource) {
			if(strpos($resource, $statics = str_replace('\\', '/', $this->commonConfig->getWebStaticDir())) === false) {
				$resourceName = basename($resource);
				$resourcePath = sprintf('%s/%s', $statics, sha1($resourcePath = str_replace($resourceName, null, $resource)));
				if(!file_exists($resourceFile = sprintf('%s/%s', $resourcePath, $resourceName))) {
					@mkdir($resourcePath, 0775, true);
					if(@symlink($resource, $resourceFile) === false) {
						throw new \Exception(sprintf('Cannot create symlink for resource [%s -> %s]; if you are on windows, webserver must have admin privileges.', $resource, $resourceFile));
					}
				}
				$resource = $resourceFile;
			}
			return $resource;
		}

		/**
		 * najde a nalinkuje vybraný zdroj do veřejné části webu (např. najde obrázek a automaticky jej publikuje); vrátí link, na kterém lze provést GET zdroje
		 *
		 * @param string $resource
		 *
		 * @return string
		 *
		 * @throws \Exception
		 */
		public function link($resource) {
			return $this->toUri($this->file($resource));
		}

		/**
		 * @param $resource - string filename
		 * @return string
		 * @throws \Exception
		 */
		public function file($resource) {
			return $this->resource($resource = $this->find($resource));
		}

		/**
		 * @param string|null $aType
		 *
		 * @return string[]
		 */
		public function getRequest($aType = null) {
			if($aType === null) {
				return $this->request;
			}
			if(!isset($this->request[$aType])) {
				return [];
			}
			return $this->request[$aType];
		}

		public function compile($type) {
			$request = $this->getRequest($type);
			if(($file = $this->cache->load($cacheId = [
					$type,
					$request
				])) !== null
			) {
				return $file;
			}
			$cache = str_replace('\\', '/', $this->resourceConfig->getPath()['']);
			@mkdir($cache, 0750, true);
			$file = sprintf('%s/%s.%s', $cache, Random::generate(), $type);
			$callback = [
				$this,
				'filter'.Strings::firstUpper($type)
			];
			foreach($request as $resource) {
				file_put_contents($file, call_user_func($callback, file_get_contents($resource)), FILE_APPEND);
			}
			return $this->cache->save($cacheId, $this->toUri($file));
		}

		protected function toUri($file) {
			return str_replace(str_replace('\\', '/', $this->resourceConfig->getPath()['wwwDir']), null, $file);
		}

		protected function filterCss($source) {
			foreach(array_filter(Strings::matchAll($source, '/(?<=url\()(\'|")?(?<url>.*?)(?=\1|\))/i')) as $match) {
				if(strpos($match['url'], 'data:') === 0) {
					continue;
				}
				$url = new Url($match['url']);
				if(($resource = $this->find(basename($url->getPath()), false)) === null) {
					continue;
				}
				$source = str_replace($match['url'], $this->toUri($this->resource($resource)), $source);
			}
			return trim(str_replace([
				'; ',
				': ',
				' {',
				'{ ',
				', ',
				' ,',
				'} ',
				' }',
				';}',
			], [
				';',
				':',
				'{',
				'{',
				',',
				',',
				'}',
				'}',
				'}',
			], preg_replace('#/\*.*?\*/#s', '', preg_replace('#\s+#', ' ', $source))));
		}

		protected function filterJs($source) {
			return $source;
		}
	}
