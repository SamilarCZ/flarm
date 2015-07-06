<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */


if (!class_exists('FLaRM\Loaders\FLaRMLoader')) {
	require __DIR__ . '/Loaders/FLaRMLoader.php';
}

//\FLaRM\Loaders\FLaRMLoader::getInstance()->register();
if (!class_exists('FLaRM\DI\AContainer')) {
    class_alias('FLaRM\DI\AContainer', 'FLaRM\AContainer');
}
require_once __DIR__ . '/shortcuts.php';
