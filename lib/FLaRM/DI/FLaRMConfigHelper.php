<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\Database\ConnectionException;
use Nette\Database\DriverException;
use Nette\Environment;
use Nette\Object;

/**
 * @author     Filip Lánský
 */
class FLaRMConfigHelper extends Object{
	public $user;
	public $password;
	public $host;
	public $driver;
	public $dbname;
	public $dsn;

	public function __construct(array $parameters = []){

		if(count($parameters) <= 0) $parameters = ((!is_null(Environment::getConfig('database'))) ? iterator_to_array(Environment::getConfig('database')) : []);
		foreach($parameters as $key => $value)
			if(property_exists($this, $key)) $this->{$key} = $value;
		$this->dsn = $this->makeDsn();
	}

	public function getDatabaseConnectionParameters(){
		return ['dsn' => $this->makeDsn(), 'user' => $this->user, 'password' => $this->password];
	}

	public function getAll(){
		return clone $this;
	}

	public function makeDsn(){
		return (string) $this->driver . ':host=' . $this->host . ';dbname=' . $this->dbname;
	}
}
