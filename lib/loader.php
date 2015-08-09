<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 * [Nette] Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

	require_once 'Nette/loader.php';


	//Nette\Utils\SafeStream::register();
	$configurator = new Nette\Configurator;

	//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
	$configurator->enableDebugger(__DIR__ . '/../log');

	$configurator->setTempDirectory(__DIR__ . '/../temp');

	$configurator->createRobotLoader()
		->addDirectory(__DIR__ . '/Nette')
		->addDirectory(__DIR__ . '/FLaRM')
		->addDirectory(__DIR__. '/../app')
//		->addDirectory(__DIR__. '/../app/model')
		->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
$configurator->addConfig(__DIR__ . '/../app/config/flarm.neon');
$configurator->addConfig(__DIR__ . '/../app/config/flarm.model.neon');

$container = $configurator->createContainer();

$router = App\RouterFactory::createRoutes();
require_once 'FLaRM/loader.php';

$flarmCompiler = new \FLaRM\DI\FLaRMCompiler(new \FLaRM\DI\FLaRMContainer($container, new \FLaRM\DI\FLaRMConfigHelper()));
$addServices = $flarmCompiler->run(true);
$container->addService('router', $router);

return $container;
