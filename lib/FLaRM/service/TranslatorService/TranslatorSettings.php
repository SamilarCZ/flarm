<?php
	namespace App\service;

	use Nette\Configurator;
	use Nette\Utils\Validators;

	class TranslatorSettings extends Configurator {
		protected function config(array $config) {
			$config = $this->parameters['translator'];
			Validators::assertField($config, 'path');
			Validators::assertField($config, 'history-path');
			Validators::assertField($config, 'default-path');
			return $config;
		}

		public function getPath() {
			return $this->parameters['path'];
		}

		public function getTextPath() {
			return $this->parameters['path'].'/text';
		}

		public function getHistoryPath() {
			return realpath($this->parameters['history-path']);
		}

		public function getHistoryExportPath() {
			return realpath($this->parameters['history-path'].'/export');
		}

		public function getHistoryImportPath() {
			return realpath($this->parameters['history-path'].'/import');
		}

		public function getDefaultPath() {
			return realpath($this->parameters['default-path']);
		}
	}
