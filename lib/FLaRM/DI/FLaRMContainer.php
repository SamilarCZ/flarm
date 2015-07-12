<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\Configurator;
use Nette\DI\Container;


/**
 * The dependency injection glue container default implementation.
 *
 * @author     Filip Lánský
 */
class FLaRMContainer extends Container{

    protected $netteContainer = [];
    public $parameters = [];
    /**
     * @var Configurator
     */
    private $configurator;

    public function __construct(Configurator $configurator = null){
        parent::__construct();
        if(isset($configurator)) {
            $this->configurator = $configurator;
            $this->netteContainer = $this->configurator->createContainer();
            $this->parameters = $this->netteContainer->parameters;
        }
    }
}
