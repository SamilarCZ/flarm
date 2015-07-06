<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\Loaders;

use FLaRM;
use Nette\Loaders\NetteLoader;


/**
 * FLaRM autoloader using Nette auto loader which is responsible for loading Nette and FLaRM classes and interfaces.
 */
abstract class FLaRMLoader extends NetteLoader{
}
