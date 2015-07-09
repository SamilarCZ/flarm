<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 * [Nette] Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

	require_once 'Nette/loader.php';
	require_once 'FLaRM/loader.php';

	//Nette\Utils\SafeStream::register();
	$configurator = new Nette\Configurator;

	//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
	$configurator->enableDebugger(__DIR__ . '/../log');

	$configurator->setTempDirectory(__DIR__ . '/../temp');

	$configurator->createRobotLoader()
		->addDirectory(__DIR__. '/../app')
		->addDirectory(__DIR__ . '/Nette')
		->addDirectory(__DIR__ . '/FLaRM')
		->register();

	$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
	$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
	$configurator->addConfig(__DIR__ . '/../app/config/flarm.neon');
	$container = $configurator->createContainer();
	$FLaRMContainer = new \FLaRM\DI\FLaRMContainer();

	$router = App\RouterFactory::createRoutes();

	$container->addService('FLaRMContainer', $FLaRMContainer);
	$container->addService('router', $router);

	return $container;