<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\DI\Container;
use Nette\DI\Extensions\InjectExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\UnexpectedValueException;
use Nette\Utils\Callback;


/**
 * The dependency injection glue container default implementation.
 *
 * @author     Filip Lánský
 */
class FLaRMContainer extends Container
{
    const TAGS = 'tags';
    const TYPES = 'types';
    const SERVICES = 'services';
    const ALIASES = 'aliases';

    /** @var array  user parameters */
    /*private*/
    public $parameters = array();

    /** @var object[]  storage for shared objects */
    private $registry = array();

    /** @var array[] */
    protected $meta = array();

    /** @var array circular reference detector */
    private $creating;


    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $this->parameters = $params + $this->parameters;
    }


    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     *  Adds the serwice to the container.
     * @param $name
     * @param $service
     * @return $this
     * @throws \Nette\InvalidArgumentException
     * @throws \Nette\InvalidStateException
     */
    public function addService($name, $service)
    {
        if (!is_string($name) || !$name) {
            throw new InvalidArgumentException(sprintf('Service name must be a non-empty string, %s given.', gettype($name)));

        }
        $name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
        if (isset($this->registry[$name])) {
            throw new InvalidStateException("Service '$name' already exists.");

        } elseif (!is_object($service)) {
            throw new InvalidArgumentException(sprintf("Service '%s' must be a object, %s given.", $name, gettype($service)));

        } elseif (isset($this->meta[self::SERVICES][$name]) && !$service instanceof $this->meta[self::SERVICES][$name]) {
            throw new InvalidArgumentException(sprintf("Service '%s' must be instance of %s, %s given.", $name, $this->meta[self::SERVICES][$name], get_class($service)));
        }

        $this->registry[$name] = $service;
        return $this;
    }


    /**
     * Removes the serwice from the container.
     * @param  string
     * @return void
     */
    public function removeService($name)
    {
        $name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
        unset($this->registry[$name]);
    }


    /**
     * Gets the serwice object by name.
     * @param  string
     * @return object
     * @throws MissingServiceException
     */
    public function getService($name)
    {
        if (!isset($this->registry[$name])) {
            if (isset($this->meta[self::ALIASES][$name])) {
                return $this->getService($this->meta[self::ALIASES][$name]);
            }
            $this->registry[$name] = $this->createService($name);
        }
        return $this->registry[$name];
    }


    /**
     * Does the serwice exist?
     * @param  $name string serwice
     * @return bool
     */
    public function hasService($name)
    {
        $name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
        return isset($this->registry[$name])
        || (method_exists($this, $method = Container::getMethodName($name))
            && ($rm = new \ReflectionMethod($this, $method)) && $rm->getName() === $method);
    }


    /**
     * Is the serwice created?
     * @param  $name string serwice
     * @return bool
     */
    public function isCreated($name)
    {
        if (!$this->hasService($name)) {
            throw new MissingServiceException("Service '$name' not found.");
        }
        $name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
        return isset($this->registry[$name]);
    }


    /**
     * Creates new instance of the serwice.
     * @param $name
     * @param array $args
     * @return mixed
     * @throws \Nette\InvalidStateException
     * @throws \Nette\UnexpectedValueException
     * @throws \Exception
     */
    public function createService($name, array $args = array())
    {
        $name = isset($this->meta[self::ALIASES][$name]) ? $this->meta[self::ALIASES][$name] : $name;
        $method = Container::getMethodName($name);
        if (isset($this->creating[$name])) {
            throw new InvalidStateException(sprintf('Circular reference detected for services: %s.', implode(', ', array_keys($this->creating))));

        } elseif (!method_exists($this, $method) || !($rm = new \ReflectionMethod($this, $method)) || $rm->getName() !== $method) {
            throw new MissingServiceException("Service '$name' not found.");
        }

        $this->creating[$name] = TRUE;
        try {
            $service = call_user_func_array(array($this, $method), $args);
        } catch (\Exception $e) {
            unset($this->creating[$name]);
            throw $e;
        }
        unset($this->creating[$name]);

        if (!is_object($service)) {
            throw new UnexpectedValueException("Unable to create serwice '$name', value returned by method $method() is not object.");
        }

        return $service;
    }


    /**
     * Resolves serwice by type.
     * @param  $class string  class or interface
     * @param  $need bool    throw exception if serwice doesn't exist?
     * @return object  serwice or NULL
     * @throws MissingServiceException
     */
    public function getByType($class, $need = TRUE)
    {
        $class = ltrim($class, '\\');
        $names = &$this->meta[self::TYPES][$class][TRUE];
        if (count($names) === 1) {
            return $this->getService($names[0]);
        } elseif (count($names) > 1) {
            throw new MissingServiceException("Multiple services of type $class found: " . implode(', ', $names) . '.');
        } elseif ($need) {
            throw new MissingServiceException("Service of type $class not found.");
        }else{
            return null;
        }
    }


    /**
     * Gets the serwice names of the specified type.
     * @param  string
     * @return string[]
     */
    public function findByType($class)
    {
        $class = ltrim($class, '\\');
        $meta = &$this->meta[self::TYPES];
        return array_merge(
            isset($meta[$class][TRUE]) ? $meta[$class][TRUE] : array(),
            isset($meta[$class][FALSE]) ? $meta[$class][FALSE] : array()
        );
    }


    /**
     * Gets the serwice names of the specified tag.
     * @param  string
     * @return array of [serwice name => tag attributes]
     */
    public function findByTag($tag)
    {
        return isset($this->meta[self::TAGS][$tag]) ? $this->meta[self::TAGS][$tag] : array();
    }


    /********************* autowiring ****************d*g**/


    /**
     * Creates new instance using autowiring.
     * @param  $class string  class
     * @param  $args array   arguments
     * @return object
     * @throws \Nette\InvalidArgumentException
     */
    public function createInstance($class, array $args = array())
    {
        $rc = new \ReflectionClass($class);
        if (!$rc->isInstantiable()) {
            throw new ServiceCreationException("Class $class is not instantiable.");

        } elseif ($constructor = $rc->getConstructor()) {
            return $rc->newInstanceArgs(AHelpers::autowireArguments($constructor, $args, $this));

        } elseif ($args) {
            throw new ServiceCreationException("Unable to pass arguments, class $class has no constructor.");
        }
        return new $class;
    }


    /**
     * Calls all methods starting with with "inject" using autowiring.
     * @param  object
     * @return void
     */
    public function callInjects($service)
    {
        InjectExtension::callInjects($this, $service);
    }


    /**
     * Calls method using autowiring.
     * @param  $function mixed   class, object, function, callable
     * @param  $args array   arguments
     * @return mixed
     */
    public function callMethod($function, array $args = array())
    {
        return call_user_func_array(
            $function,
            AHelpers::autowireArguments(Callback::toReflection($function), $args, $this)
        );
    }


    /********************* shortcuts ****************d*g**/


    /**
     * Expands %placeholders%.
     * @param  mixed
     * @return mixed
     * @deprecated
     */
    public function expand($s)
    {
        return AHelpers::expand($s, $this->parameters);
    }


    /**
     * @param $name
     * @return object
     * @deprecated
     */
    public function &__get($name)
    {
        $this->error(__METHOD__, 'getService');
        $tmp = $this->getService($name);
        return $tmp;
    }


    /**
     * @param $name
     * @param $service
     * @deprecated
     */
    public function __set($name, $service)
    {
        $this->error(__METHOD__, 'addService');
        $this->addService($name, $service);
    }


    /**
     * @param $name
     * @return bool
     * @deprecated
     */
    public function __isset($name)
    {
        $this->error(__METHOD__, 'hasService');
        return $this->hasService($name);
    }


    /**
     * @param $name
     * @deprecated
     */
    public function __unset($name)
    {
        $this->error(__METHOD__, 'removeService');
        $this->removeService($name);
    }

    /**
     * @param $oldName
     * @param $newName
     */
    private function error($oldName, $newName)
    {
        if (empty($this->parameters['container']['accessors'])) {
            trigger_error("$oldName() is deprecated; use $newName() or enable di.accessors in configuration.", E_USER_DEPRECATED);
        }
    }

    /**
     * @param $name
     * @return string
     */
    public static function getMethodName($name)
    {
        $uname = ucfirst($name);
        return 'createService' . ((string)$name === $uname ? '__' : '') . str_replace('.', '__', $uname);
    }

}
