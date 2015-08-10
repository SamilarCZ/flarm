<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;
use Nette\Database\Connection;


/**
 * The compiler and generator of model layer of application created from database structure
 *
 * @author     Filip Lánský
 */
class FLaRMCompiler extends FLaRMContainer{
	/**
	 * @var Connection
	 */
    private $connection;
	/**
	 * @var FLaRMContainer
	 */
	private $FLaRMContaier;

	public $parameters = [];

	private $compilerCache;

    public function __construct(FLaRMContainer $FLaRMContainer){
		$this->parameters = $FLaRMContainer->getParameters();
		$this->FLaRMContaier = $FLaRMContainer;
		$this->connection = $this->FLaRMContaier->createConnection();
	}

    public function run($forceReload = false){
        if(isset($this->connection)) {
			if (!file_exists($this->getModelDirectory() . '/BaseModel.php') || $forceReload === true) {
				$baseModelFile = fopen($this->getModelDirectory() . '/BaseModel.php', 'w');
				fputs($baseModelFile, $this->loadCompilerTemplate('BaseModel'));
				fclose($baseModelFile);
			}
			$servicesArray = [];
			$modelWrapperArray['property'] = [];
			$modelWrapperArray['inject'] = [];
			$modelWrapperArray['body'] = [];
			foreach ($this->getTableNamesForCompiler() as $key => $table) {
				// generate model of tables here
				if (!file_exists($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php') || $forceReload === true) {
					$servicesArray[] = $this->getTableNameToClassName($table['name']) . ': FLaRM\\Model\\' . $this->getTableNameToClassName($table['name']) . PHP_EOL;
					$modelFile = fopen($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php', 'w');
					$deleteMethodBody = [];
					$loadMethodBody = [];
					$saveMethodBody = [];
					$properties = '';
					foreach ($this->getColumnNamesInGivenTable($table['name']) as $column) {
						$this->compilerCache = $this->getTableNameToClassName($table['name']);
						$primary = (($column['primary'] === TRUE) ? PHP_EOL . '        *   @primary TRUE' : '');
						if($primary) $properties .= $this->loadCompilerTemplate('Model-properties', ['column' => $column['vendor']['Field'], 'primary' => $primary]);
						else $properties .= $this->loadCompilerTemplate('Model-properties', ['column' => $column['vendor']['Field'], 'primary' => '']);
					}
					// INFO: COMPILER TEMPLATE MODEL HEADER
					fputs($modelFile, $this->loadCompilerTemplate('Model-header', ['tableName' => $table['name'], 'properties' => $properties]));
					// INFO: COMPILER TEMPLATE MODEL HEADER
					fputs($modelFile, $this->loadCompilerTemplate('Model-setArrayToXX'));
					fputs($modelFile, $this->loadCompilerTemplate('Model-setArrayToXXs'));
					$modelWrapperArray['property'][$key] = strtolower(substr($this->getTableNameToClassName($table['name']), 0, 1)) . substr($this->getTableNameToClassName($table['name']), 1);
					$modelWrapperArray['inject'][$key] = $this->getTableNameToClassName($table['name']);
					// generate native column methods

					foreach ($this->getColumnNamesInGivenTable($table['name']) as $column) {
						$method = $this->getColumnForeignRelationsMethods($column, $table['name']);
						if ($method !== false) $modelWrapperArray['body'][$key][] = $method;
						if(!isset($deleteMethodBody['unsetCommands'])) $deleteMethodBody['unsetCommands'] = '				unset($this->' . $column['vendor']['Field'] . ');' . PHP_EOL;
						else $deleteMethodBody['unsetCommands'] .= '				unset($this->' . $column['vendor']['Field'] . ');' . PHP_EOL;
						if ($column['vendor']['Field'] != 'id') {
							if(!isset($loadMethodBody['setters']))$loadMethodBody['setters'] = '					$this->set' . $this->getColumnNameToMethodName($column['vendor']['Field']) . '($activeRow->' . $column['vendor']['Field'] . ');' . PHP_EOL;
							else $loadMethodBody['setters'] .= '					$this->set' . $this->getColumnNameToMethodName($column['vendor']['Field']) . '($activeRow->' . $column['vendor']['Field'] . ');' . PHP_EOL;
							if(!isset($saveMethodBody['arraySet']))$saveMethodBody['arraySet'] = '			$values[\'' . $column['vendor']['Field'] . '\'] = $this->' . $column['vendor']['Field'] . ';' . PHP_EOL;
							else $saveMethodBody['arraySet'] .= '			$values[\'' . $column['vendor']['Field'] . '\'] = $this->' . $column['vendor']['Field'] . ';' . PHP_EOL;
						}
						$getterSetterParams['columnMethod'] = $this->getColumnNameToMethodName($column['vendor']['Field']);
						$getterSetterParams['column'] = $column['vendor']['Field'];
						$getterSetterParams['nativeType'] = $this->translateReturnValueOfColumn($column['nativetype']);

						// GETTER
						fputs($modelFile, $this->loadCompilerTemplate('Model-getXXMethod', $getterSetterParams));
						// SETTER
						fputs($modelFile, $this->loadCompilerTemplate('Model-setXXMethod', $getterSetterParams));
						// AND WHERE COLUMN EQUAL
						fputs($modelFile, $this->loadCompilerTemplate('Model-andWhereXXIsEquallMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// AND WHERE COLUMN NOT EQUAL
						fputs($modelFile, $this->loadCompilerTemplate('Model-andWhereXXIsNOTEquallMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// AND WHERE COLUMN IN
						fputs($modelFile, $this->loadCompilerTemplate('Model-andWhereXXIsInMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// AND WHERE COLUMN NOT IN
						fputs($modelFile, $this->loadCompilerTemplate('Model-andWhereXXIsNOTInMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// OR WHERE COLUMN EQUAL
						fputs($modelFile, $this->loadCompilerTemplate('Model-orWhereXXIsEquallMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// OR WHERE COLUMN NOT EQUAL
						fputs($modelFile, $this->loadCompilerTemplate('Model-orWhereXXIsNOTEquallMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// OR WHERE COLUMN IN
						fputs($modelFile, $this->loadCompilerTemplate('Model-orWhereXXIsInMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
						// OR WHERE COLUMN NOT IN
						fputs($modelFile, $this->loadCompilerTemplate('Model-orWhereXXIsNOTInMethod', ['column' => $column['vendor']['Field'], 'columnMethod' => $this->getColumnNameToMethodName($column['vendor']['Field'])]));
					}
					fputs($modelFile, $this->loadCompilerTemplate('Model-getSqlMethod'));
					fputs($modelFile, $this->loadCompilerTemplate('Model-saveMethod', $saveMethodBody));
					fputs($modelFile, $this->loadCompilerTemplate('Model-loadMethod', $loadMethodBody));
					fputs($modelFile, $this->loadCompilerTemplate('Model-deleteMethod', $deleteMethodBody));
					fputs($modelFile, $this->loadCompilerTemplate('Model-loadAllMethod'));
					fputs($modelFile, $this->loadCompilerTemplate('Model-getModelMethod'));
					fputs($modelFile, $this->loadCompilerTemplate('Model-queryMethod'));

					fputs($modelFile,
						'	}

');
					fclose($modelFile);
				}
			}
			if(!file_exists($this->getModelDirectory(). '/ModelFactoryWrapper.php') || $forceReload === true){
				$modelWrapperFile = fopen($this->getModelDirectory() . '/ModelFactoryWrapper.php', 'w');
				$construct_params = [];
				$modelWrapperHeader = ['property' => '', 'construct' => '', 'wrapper' => '', 'body' => ''];
				$construct = ['implodeParams' => '', 'uses' => ''];
				$construct_calls = [];
				foreach ($modelWrapperArray['property'] as $k => $v) {
					$property['factoryModelName'] = $modelWrapperArray['inject'][$k];
					$property['property'] = $v;
					$modelWrapperHeader['property'] .= $this->loadCompilerTemplate('ModelFactoryWrapper-properties', $property);
					$construct_params[] = $modelWrapperArray['inject'][$k] . ' $' . $v;
					$construct_calls[] = '$this->' . $v . ' = $' . $v . ';';
				}
				foreach ($construct_calls as $v) {
					$construct['uses'] .= '			' . $v . PHP_EOL;
				}
				$construct['implodeParams'] = implode(',', $construct_params);
				$modelWrapperHeader['construct'] = $this->loadCompilerTemplate('ModelFactoryWrapper-__construct', $construct);
				$wrapper = ['factoryModelName' => '', 'factoryModelNameProperty' => ''];
				foreach ($modelWrapperArray['property'] as $k => $v) {
					$wrapper['factoryModelName'] = $modelWrapperArray['inject'][$k];
					$wrapper['factoryModelNameProperty'] = $v;
					$modelWrapperHeader['wrapper'] .= $this->loadCompilerTemplate('ModelFactoryWrapper-wrapper', $wrapper);
				}
				foreach ($modelWrapperArray['body'] as $key => $val) {
					foreach ($val as $k => $v) {
						$modelWrapperHeader['body'] .= PHP_EOL . '		' . $v . '' . PHP_EOL;
					}
				}
				fputs($modelWrapperFile, $this->loadCompilerTemplate('ModelFactoryWrapper', $modelWrapperHeader));
				$flarmNeon = fopen($this->getConfigDirectory() . '/flarm.model.neon', 'w');
				fputs($flarmNeon, 'services:' . PHP_EOL);
				foreach($servicesArray as $serviceString) {
					fputs($flarmNeon, '	' . $serviceString);
				}
				fputs($flarmNeon, '	' . 'ModelFactoryWrapper: FLaRM\\Model\\ModelFactoryWrapper');
			}
            return $servicesArray;
        }
        return false;
    }

    private function getTableNamesForCompiler(){
        return $this->connection->getSupplementalDriver()->getTables();
    }

    private function getColumnNamesInGivenTable($table){
        return $this->connection->getSupplementalDriver()->getColumns($table);
    }

    private function getModelDirectory(){
        return $this->parameters['appDir'] . '/../app/model';
    }

    private function getConfigDirectory(){
        return $this->parameters['appDir'] . '/../app/config';
    }

    private function getTableNameToClassName($table){
        $className = false;
        $chunks = explode('_', $table);
        if(is_array($chunks)){
            foreach($chunks as $piece) $className .= ucfirst($piece);
        }
        $className .= 'Model';
        return $className;
    }

    private function getColumnNameToMethodName($column){
        $className = false;
        $chunks = explode('_', $column);
        if(is_array($chunks)){
            foreach($chunks as $piece) $className .= ucfirst($piece);
        }
        return $className;
    }

    public function translateReturnValueOfColumn($type){
        switch ($type){
            case 'INT':
                return 'integer';
            case 'FLOAT':
                return 'float';
            case 'DATETIME':
                return 'string';
            case 'DATE':
                return 'string';
            case 'VARCHAR':
                return 'string';
            case 'TEXT':
                return 'string';
        }
        return false;
    }

    public function getColumnForeignRelationsMethods($column, $table){
        $chunks = explode('_', $column['vendor']['Field']);
        $method = null;
        if(is_array($chunks) && count($chunks) > 1){
            if(end($chunks) === 'id'){
                // TODO : MAKE RELATION METHOD HERE ! - idea : just inject other model this way into the final object
//                dump($column);
				$method =
		'/**
        *   @var $id ' . $this->translateReturnValueOfColumn($column['nativetype']) . '
        *   @return array|null
        */
        public function getRelated' . str_replace('Id', '', $this->getColumnNameToMethodName($column['vendor']['Field']))  . '($id = null){
			return $this->' . $this->getRelatedTableModelNameFromIdColumn($column['vendor']['Field']) . '->ref(\'' . $this->getRelatedTableNameFromIdColumn($column['vendor']['Field']) . '\', \'' . $column['vendor']['Field'] . '\', $id);
        }';
            }
        }

        return (($method) ? $method : false);
    }

    private function getRelatedTableNameFromIdColumn($column){
        return str_replace('_id', '', $column);
    }

    private function getRelatedTableModelNameFromIdColumn($column){
        return str_replace('_id', 'Model', $column);
    }

	private function loadCompilerTemplate($template, $params = []){
		$template = str_replace('{modelName}', $this->compilerCache, file_get_contents($this->FLaRMContaier->getParameters()['appDir'] . '/FLaRM/templates/compiler/' . $template . '.compiler'));
		if(count($params) > 0){
			foreach($params as $key => $value){
				$template = str_replace('{' . $key . '}', $value, $template);
			}
		}
		return $template;
	}
}
