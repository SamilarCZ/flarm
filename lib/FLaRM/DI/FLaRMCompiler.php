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

    public function __construct(FLaRMContainer $FLaRMContainer){
		$this->parameters = $FLaRMContainer->getParameters();
		$this->FLaRMContaier = $FLaRMContainer;
		$this->connection = $this->FLaRMContaier->createConnection();
	}

    public function run($forceReload = false){
        if(isset($this->connection)) {
            if(!file_exists($this->getModelDirectory(). '/BaseModel.php') || $forceReload === true){
                $baseModelFile = fopen($this->getModelDirectory() . '/BaseModel.php', 'w');
                fputs($baseModelFile,
'<?php
    namespace App\Model;

    use Nette\Database\Context;
	use Nette\Database\Table\ActiveRow;
	use Nette\Database\Table\IRow;
	use Nette\Database\Table\Selection;
	use Nette\InvalidStateException;
	use Nette\Object;
	use Traversable;

    abstract class BaseModel extends Object
    {
        /** @var Context */
        protected $database;

        /** @var string */
        protected $tableName;


        /**
         * @param $database Context
         */
        public function __construct(Context $database){

            $this->database = $database;
            $this->tableName = $this->tableNameByClass(get_class($this));
        }


        /**
         * Určí tabulku dle názvu třídy
         * @param string
         * @return string
         * @result: Pages => pages, ArticleTag => article_tag
         */
        private function tableNameByClass($className){
            $tableName = explode("\\\\", $className);
            $tableName = lcfirst(array_pop($tableName));

            $replace = array(); // A => _a
            foreach (range("A", "Z") as $letter) {
                $replace[$letter] = "_" . strtolower($letter);
            }
			$tableName = str_replace("Model","",$tableName);
			$tableName = str_replace("model","",$tableName);
            return strtr($tableName, $replace);
        }
		// TODO : MAKE THIS METHODS
        public function delete(){

        }
        public function save(){}
		public function load(){}

		public function select($args){
			return $this->getTable()->select($args);
        }

        // přidáme vlastní metody: insert, update, delete, count,
        // fetchSingle, fetchPairs atd.
		/**
		 * Table rows count getter
		 *
		 * @return integer
		 */
		public function count(){
			return $this->getTable()->count();
		}

		/**
		 * Return item by primary key
		 *
		 * @param integer $key
		 * @return ActiveRow
		 */
		public function get($key){
			return $this->getTable()->get($key);
		}

		/**
		 * Alias of <\b>getTable\<\/b>
		 *
		 * @return Selection
		 */
		public function getAll(){
			return $this->getTable();
		}

		/**
		 * Vrací vyfiltrované záznamy na základě vstupních parametrů
		 * @param string $key
		 * @param string $val
		 * @return \Nette\Database\Table\Selection
		 */
		public function fetchPairs($key=null,$val=null){
			return $this->getTable()->fetchPairs($key,$val);

		}

		public function fetchAssoc($path){
			return $this->getTable()->fetchAssoc($path);
		}

		public function fetch(){
			return $this->getTable()->fetch();
		}

		public function fetchAll(){
			return $this->getTable()->fetchAll();
		}
		public function findBy($by){
			return $this->getTable()->where($by);
		}

		public function findOneBy($by){
			return $this->getTable()->where($by)->fetch();
		}

		/**
		 * Table getter
		 *
		 * @return Selection
		 */
		public function getTable(){
			return $this->getDatabase()->table($this->getTableName());
		}

		/**
		 * Inserts row in a table.
		 *
		 * @param  array|Traversable|Selection array($column => $value)|\Traversable|Selection for INSERT ... SELECT
		 * @return IRow|int|bool Returns IRow or number of affected rows for Selection or table without primary key
		 */
		public function insert($data){
			return $this->getTable()->insert($data);
		}

		/**
		 * Sets limit clause, more calls rewrite old values.
		 *
		 * @param integer
		 * @param integer [OPTIONAL]
		 * @return Selection
		 */
		public function limit($limit, $offset = NULL){
			return $this->getTable()->limit($limit, $offset);
		}

		/**
		 * Zkratka pro where
		 *
		 * @param string $order
		 * @return Selection
		 */
		public function order($order){
			return $this->getTable()->order($order);
		}

		public function group($order){
			return $this->getTable()->group($order);
		}
		/**
		 * Update data in database
		 *
		 * @param array $data
		 * @return Selection
		 */
		public function update($data){
			return $this->getTable()->update($data);
		}

		/**
		 * Search for row in the table
		 *
		 * @param string $condition
		 * @param array $parameters
		 * @return Selection
		 */
		public function where($condition, $parameters = array()){
			return call_user_func_array(array($this->getTable(), \'where\'), func_get_args());
		}

		// <\editor-fold defaultstate="collapsed" desc="Getters & Setters">
		/**
		 * Database getter
		 *
		 * @return Context
		 */
		private function getDatabase(){
			return $this->database;
		}

		/**
		 * Database setter
		 *
		 * @param Context $database
		 * @return BaseModel Provides fluent interface
		 * @throws InvalidStateException
		 */
		private function setDatabase(Context $database){
			if ($this->database !== NULL)
			{
				throw new InvalidStateException(\'Database has already been set\');
			}
			$this->database = $database;
			return $this;
		}

		/**
		 * Table name getter
		 *
		 * @return string
		 */
		public function getTableName(){
			return $this->tableName;
		}

		/**
		* Table to table relation
		* @param $table string
		* @param $column string
		* @return array|null
		*/
		public function ref($table, $column, $id = null){
			$model = $this;
			if(!is_null($id)) $model->where($column, $id);
			return $model->fetch()->getReferencingTable($table, $column);
		}
		// <\/editor-fold>
    }
'
                    );
            }
			$servicesArray = [];
			$modelWrapperArray['property'] = [];
			$modelWrapperArray['inject'] = [];
			$modelWrapperArray['body'] = [];
            foreach ($this->getTableNamesForCompiler() as $key => $table) {
                // generate model of tables here
                if (!file_exists($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php') || $forceReload === true){
					$servicesArray[] = $this->getTableNameToClassName($table['name']) . ': App\\Model\\' . $this->getTableNameToClassName($table['name']) . PHP_EOL;
					$modelFile = fopen($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php', 'w');

					$modelFileHeader =
'<?php
    namespace App\Model;

    class ' . $this->getTableNameToClassName($table['name']) . ' extends BaseModel{

		/** @var string */
		protected $tableName = \'' . $table['name'] . '\';

		/** property */

		public $data = [];
		public $where;

';
                    fputs($modelFile, $modelFileHeader);

                    foreach($this->getColumnNamesInGivenTable($table['name']) as $column) {
                        fputs($modelFile,
'       /**' . (($column['primary'] === TRUE)? '
        *   @primary TRUE' : '')  . '
        */
        private $' . $column['vendor']['Field'] . ';
');
                    }
                    fputs($modelFile,
'

		/** end property */

		/**
		 * @param array $setArray
		 * @return array|BlocksModel
		 */
		public function setArrayTo' . $this->getTableNameToClassName($table['name']) . 's(array $setArray = []){
			if(count($setArray) > 0){
				$newObjects = [];
				foreach($setArray as $key => $value){
					if(is_array($value)){
						if(isset($value[\'id\'])){
							$newModel = $this->createEmptyModel();
							$newModel->data = $value;
							$newObjects[] = clone $newModel;
						}
					}
				}
				return $newObjects;
			}
		}

		/**
		 * @param array $setArray
		 * @return BlocksModel
		 */
		public function setArrayTo' . $this->getTableNameToClassName($table['name']) . '(array $setArray = []){
			if(is_array($setArray)){
				$newObjects = [];
				if(isset($setArray[\'id\'])){
					$newModel = $this->createEmptyModel();
					$newModel->data = $setArray;
					$newObjects[] = clone $newModel;
				}
				return $newObjects;
			}
		}

');
					$modelWrapperArray['property'][$key] = strtolower(substr($this->getTableNameToClassName($table['name']),0,1)) . substr($this->getTableNameToClassName($table['name']),1);
					$modelWrapperArray['inject'][$key] = $this->getTableNameToClassName($table['name']);
                    // generate native column methods
					$createMethodBegin =
'		/**
		 * @return $this
		 */
		public function createEmptyModel(){' . PHP_EOL .
'			return new ' . $this->getTableNameToClassName($table['name']) . '($this->database);
';
					$createMethodBody =
'';
					$createMethodEnd =
'		}';
					$deleteMethodBegin =
'		/**
		 * @return bool
		 */
		public function deĺete(){' . PHP_EOL .
'			if($this->getId()){
				$this->where(\'id=?\', $this->id)->delete();
';
					$deleteMethodBody = '';
					$deleteMethodEnd =
'				return true;
			} else {
				return false;
			}
		}';
					$loadAllMethodBegin =
						'		/**
		 * @return \\stdClass
		 */
		public function loadAll(){
			$activeRow = $this->fetchAll();
			if($activeRow !== false){
				$data = [];
				foreach($activeRow as $key => $value){
					$data[$key] = iterator_to_array($value);
				}
				return $this->setArrayTo' . $this->getTableNameToClassName($table['name']) . 's($data);
			}
';
					$loadAllMethodBody = '';
					$loadAllMethodEnd =
'		}';
					$loadMethodBegin =
'		/**
		 * @return \\stdClass
		 */
		public function load(){
			if($this->getId()){
				$activeRow = $this->where(\'id=?\', $this->id)->fetch();
				if($activeRow !== false){
';
					$loadMethodBody = '';
					$loadMethodEnd =
'				}
				unset($activeRow);
				return json_decode(json_encode($this->data));
			} else {
				return json_decode(json_encode($this->data));
			}
		}
';
					$saveMethodBegin =
'		public function save(){
			$values = [];
';
					$saveMethodBody = '';
					$saveMethodEnd =
'			if($this->id) $this->where(\'id=?\',$this->id)->update($values);
			else {
				$activeRow = $this->insert($values);
				$this->data[\'id\'] = $this->id = $activeRow->getPrimary();
			}
		}
';
                    foreach($this->getColumnNamesInGivenTable($table['name']) as $column){
						$method = $this->getColumnForeignRelationsMethods($column, $table['name']);
						if($method !== false) $modelWrapperArray['body'][$key][] = $method;
						$deleteMethodBody .=
'				unset($this->' . $column['vendor']['Field'] . ');
';
						if($column['vendor']['Field'] != 'id'){
							$loadMethodBody .=
'					$this->set' . $this->getColumnNameToMethodName($column['vendor']['Field']) . '($activeRow->' . $column['vendor']['Field'] . ');
';

							$saveMethodBody .=
'			$values[\'' . $column['vendor']['Field'] . '\'] = $this->' . $column['vendor']['Field'] . ';
';
						}
						$getter =
'
        /**
        *   @return ' . $this->translateReturnValueOfColumn($column['nativetype']) . '
        */
        public function get' . $this->getColumnNameToMethodName($column['vendor']['Field']) . '(){
            if(isset($this->data[\'' . $column['vendor']['Field'] . '\'])){
            	return $this->data[\'' . $column['vendor']['Field'] . '\'];
            }else if($this->' . $column['vendor']['Field'] . '){
				return $this->' . $column['vendor']['Field'] . ';
			} else {
				return false;
			};
        }';
						$setter =
'
        /**
        *	@var ' . $this->translateReturnValueOfColumn($column['nativetype']) . ' $value
        *	@return $this
        */
        public function set' . $this->getColumnNameToMethodName($column['vendor']['Field']) . '($value){
            return $this->data[\'' . $column['vendor']['Field'] . '\'] = $this->' . $column['vendor']['Field'] . ' = $value;
        }';
						fputs($modelFile, $getter . $setter);
					}
					fputs($modelFile, PHP_EOL . PHP_EOL . $saveMethodBegin . $saveMethodBody . $saveMethodEnd . PHP_EOL);
					fputs($modelFile, PHP_EOL . PHP_EOL . $loadMethodBegin . $loadMethodBody . $loadMethodEnd . PHP_EOL);
					fputs($modelFile, PHP_EOL . PHP_EOL . $loadAllMethodBegin . $loadAllMethodBody . $loadAllMethodEnd . PHP_EOL);
					fputs($modelFile, PHP_EOL . PHP_EOL . $deleteMethodBegin . $deleteMethodBody . $deleteMethodEnd . PHP_EOL);
					fputs($modelFile, PHP_EOL . PHP_EOL . $createMethodBegin . $createMethodBody . $createMethodEnd . PHP_EOL);
                    fputs($modelFile,
'	}

');
				}
			}
			$modelWrapperFile = fopen($this->getModelDirectory() . '/ModelFactoryWrapper.php', 'w');
			$modelWrapperFileHeader =
				'<?php
    namespace App\Model;

	use Nette;
';
//			foreach($modelWrapperArray['inject'] as $k => $v) {
//				$modelWrapperFileHeader .=
//'	use App\\Model\\' . $v . ';
//';
//			}
$modelWrapperFileHeader .=
'
    class ModelFactoryWrapper{

';
			fputs($modelWrapperFile, $modelWrapperFileHeader);
			$construct_params = [];
			foreach($modelWrapperArray['property'] as $k => $v){
				fputs($modelWrapperFile,
'		/**
		* @var ' . $modelWrapperArray['inject'][$k] . '
		*/
		protected $' . $v . ';

');
				$construct_params[] = $modelWrapperArray['inject'][$k] . ' $' . $v;
				$construct_calls[] = '$this->' . $v . ' = $' . $v . ';';
			}

			fputs($modelWrapperFile,
'
		public function __construct(' . implode(',', $construct_params) . '){'
);
			foreach($construct_calls as $v){
				fputs($modelWrapperFile,
 PHP_EOL . '			' . $v . ''
				);
			}
			fputs($modelWrapperFile,
'
		}

');
			foreach($modelWrapperArray['property'] as $k => $v){
//				foreach($val as $k => $v) {
					fputs($modelWrapperFile,
						PHP_EOL . PHP_EOL .
						'		/**' . PHP_EOL .
						'		* @return ' . $modelWrapperArray['inject'][$k] . PHP_EOL .
						'		*/' . PHP_EOL .
						'		public function ' . $v . '(){' . PHP_EOL . PHP_EOL .
						'			return $this->' . $v . ';' . PHP_EOL . PHP_EOL .
						'		}'
					);
//				}
			}
			foreach($modelWrapperArray['body'] as $key => $val){
				foreach($val as $k => $v) {
					fputs($modelWrapperFile,
						PHP_EOL . '		' . $v . '' . PHP_EOL
					);
				}
			}
			fputs($modelWrapperFile,
				PHP_EOL . PHP_EOL . '}'
			);
			@chmod($this->getConfigDirectory() . '/', 0777);
			$flarmNeon = fopen($this->getConfigDirectory() . '/flarm.model.neon', 'w');
			fputs($flarmNeon, 'services:' . PHP_EOL);
			foreach($servicesArray as $serviceString) {
				fputs($flarmNeon, '	' . $serviceString);
			}
			fputs($flarmNeon, '	' . 'ModelFactoryWrapper: App\Model\ModelFactoryWrapper');
			@chmod($this->getConfigDirectory() . '/flarm.neon', 0755);
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
}
