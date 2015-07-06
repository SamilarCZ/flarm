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
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
$configurator->addConfig(__DIR__ . '/../app/config/flarm.neon');

$container = $configurator->createContainer();

require_once 'FLaRM/loader.php';
$router = new \Nette\Application\Routers\RouteList();
//\App\RouterFactory::createRouter($router);
$router[] = new \Nette\Application\Routers\Route('<presenter>/<action>[/<id>]', 'Homepage:index');
$router[] = new \Nette\Application\Routers\Route('index.php', 'Index:index', Route::ONE_WAY);
$router[] = new \Nette\Application\Routers\Route('<action>', 'Index:index');
//$container->addService('router', $router);
return $container;