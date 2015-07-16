<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\DI\Container;


/**
 * The dependency injection glue container default implementation.
 *
 * @author     Filip Lánský
 */
class FLaRMContainer extends Container{
	/**
	 * @var array|Container
	 */
    protected $netteContainer;
	/**
	 * @var FLaRMConfigHelper
	 */
	private $FLaRMConfigHelper;

    public function __construct(Container $container, FLaRMConfigHelper $FLaRMConfigHelper){
        parent::__construct();
		$this->netteContainer = $container;
		$this->parameters = $this->netteContainer->parameters;
        $this->FLaRMConfigHelper = $FLaRMConfigHelper->getDatabaseConnectionParameters();
	}

	function createConnection(){
		dump($this->FLaRMConfigHelper);
//		return new Connection(
//			$this->FLaRMConfigHelper->dsn,
//			$this->FLaRMConfigHelper->user,
//			$this->FLaRMConfigHelper->password
//		);
	}

	public function getParameters(){
		return $this->parameters;
	}
}
