<?php

	/**
	 * This file is part of the Kdyby (http://www.kdyby.org)
	 *
	 * Copyright (c) 2015 Filip Lánský
	 *
	 */
	namespace Nette\DI;

	use App\exception\Resource\InvalidResourceException;
	use Nette;
	use Nette\DI\Statement;
	use Nette\PhpGenerator as Code;
	use Nette\Reflection;
	use Nette\Utils\Arrays;
	use Nette\Utils\Callback;
	use Nette\Utils\Finder;
	use Nette\Utils\Strings;
	use Nette\Utils\Validators;
	use Tracy;

	/**
	 * @author Filip Lánský
	 */
	class ResourceExtension extends Nette\DI\CompilerExtension {
		const LOADER_TAG = 'resource.loader';
		const DUMPER_TAG = 'resource.dumper';
		const EXTRACTOR_TAG = 'resource.extractor';
		const RESOLVER_REQUEST = 'request';
		const RESOLVER_HEADER = 'header';
		const RESOLVER_SESSION = 'session';
		/**
		 * @var array
		 */
		public $defaults = array(
			// 'whitelist' => array('cs', 'en'),
			'default' => 'en',
			// 'fallback' => array('en_US', 'en'), // using custom merge strategy becase Nette's config merger appends lists of values
			'dirs' => array(
				'%appDir%/lang',
				'%appDir%/locale'
			),
			'cache' => '%tempDir%',
			'debugger' => '%debugMode%',
			'resolvers' => array(
				self::RESOLVER_SESSION => false,
				self::RESOLVER_REQUEST => true,
				self::RESOLVER_HEADER => true,
			),
		);
		/**
		 * @var array
		 */
		private $loaders;

		public function __construct() {
			$this->defaults['cache'] = new Statement($this->defaults['cache'], array('%tempDir%/cache'));
		}

		public function loadConfiguration() {
			$this->loaders = array();
			$builder = $this->getContainerBuilder();
			$config = $this->getConfig();

			$latteFactory = $builder->hasDefinition('nette.latteFactory') ? $builder->getDefinition('nette.latteFactory') : $builder->getDefinition('nette.latte');
			$latteFactory->addSetup('App\service\ResourceService\ResourceMacro::install(?->getCompiler())', array('@self'));
		}


		public function beforeCompile() {
			$builder = $this->getContainerBuilder();
			$config = $this->getConfig();
			foreach($builder->findByTag(self::DUMPER_TAG) as $dumperId => $meta) {
				Validators::assert($meta, 'string:2..');
				$builder->getDefinition($dumperId)
					->setAutowired(false)
					->setInject(false);
			}
			if($dirs = array_values(array_filter($config['dirs'], Callback::closure('is_dir')))) {
				foreach($dirs as $dir) {
					$builder->addDependency($dir);
				}
				foreach(Arrays::flatten($this->loaders) as $format) {
					foreach(Finder::findFiles('*.*.'.$format)
								->from($dirs) as $file) {
						/** @var \SplFileInfo $file */
						if($m = Strings::match($file->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.'.preg_quote($format).'$~')) {
							if(!in_array(substr($m['locale'], 0, 2), $config['whitelist'])) {
								if($config['debugger']) {
									$builder->getDefinition($this->prefix('panel'))
										->addSetup('addIgnoredResource', array(
											$format,
											$file->getPathname(),
											$m['locale'],
											$m['domain']
										));
								}
								continue; // ignore
							}
							$this->validateResource($format, $file->getPathname(), $m['locale'], $m['domain']);
							$builder->addDependency($file->getPathname());
							if($config['debugger']) {
								$builder->getDefinition($this->prefix('panel'))
									->addSetup('addResource', array(
										$format,
										$file->getPathname(),
										$m['locale'],
										$m['domain']
									));
							}
						}
					}
				}
			}
		}

		protected function validateResource($format, $file, $locale, $domain) {
			$builder = $this->getContainerBuilder();
			foreach($this->loaders as $id => $knownFormats) {
				if(!in_array($format, $knownFormats, true)) {
					continue;
				}
				try {
					$def = $builder->getDefinition($id);
					$refl = Reflection\ClassType::from($def->factory ? $def->factory->entity : $def->class);
					if(($method = $refl->getConstructor()) && $method->getNumberOfRequiredParameters() > 1) {
						continue;
					}
					$loader = $refl->newInstance();
				} catch(\ReflectionException $e) {
					continue;
				}
				try {
					$loader->load($file, $locale, $domain);
				} catch(\Exception $e) {
					throw new InvalidResourceException("Resource $file is not valid and cannot be loaded.", 0, $e);
				}
				break;
			}
		}

		public function afterCompile(Code\ClassType $class) {
			$class->methods['initialize'];
		}

		/**
		 * {@inheritdoc}
		 */
		public function getConfig(array $defaults = null, $expand = true) {
			return parent::getConfig($this->defaults);
		}

		/**
		 * @param string|\stdClass $statement
		 *
		 * @return Nette\DI\Statement[]
		 */
		public static function filterArgs($statement) {
			return Nette\DI\Compiler::filterArguments(array(is_string($statement) ? new Nette\DI\Statement($statement) : $statement));
		}

		/**
		 * @param \Nette\Configurator $configurator
		 */
		public static function register(Nette\Configurator $configurator) {
			$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
				$compiler->addExtension('resource', new ResourceExtension());
			};
		}
	}
