<?php
	namespace App\service\ResourceService;

	use Nette\Bridges\ApplicationDI\RoutingExtension;

	class ResourceRouter extends RoutingExtension {
		public function __construct() {
			parent::__construct('/static/<.*>/<resource>', array(
				'module' => 'Front',
				'presenter' => 'Resource',
				'action' => 'static',
			));
		}
	}
