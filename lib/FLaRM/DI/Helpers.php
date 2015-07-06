<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\DI\Helpers;

/**
 * The DI helpers glue for Nette\DI\Helpers which is marked as @internal.
 *
 * @author     Filip Lánský
 * {@inheritDoc Helpers Nette helpers with @internal attribute }
 */
class AHelpers extends Helpers{

    /**
     * @param $var
     * @param array $params
     * @param bool $recursive
     * @return mixed
     */
    public static function expand($var, array $params, $recursive = FALSE){
        return parent::expand($var, $params, $recursive);
    }

    /**
     * @param \ReflectionFunctionAbstract $method
     * @param array $arguments
     * @param $container
     * @return array
     */
    public static function autowireArguments(\ReflectionFunctionAbstract $method, array $arguments, $container){
        return parent::autowireArguments($method, $arguments, $container);
    }
}
