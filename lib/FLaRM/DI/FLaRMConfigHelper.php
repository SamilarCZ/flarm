<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\Environment;
use Nette\Object;

/**
 * @author     Filip Lánský
 */
class FLaRMConfigHelper extends Object{
	public $dsn;
	public $user;
	public $password;

	public function __construct(array $parameters){
        $parameters = Environment::getConfig('db');
		list($this->dsn, $this->user, $this->password) = $parameters;
	}

	public function getDatabaseConnectionParameters(){
		return ['dsn' => $this->dsn, 'user' => $this->user, 'password' => $this->password];
	}
}
