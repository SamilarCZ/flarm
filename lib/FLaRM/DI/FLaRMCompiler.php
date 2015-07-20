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
        private $tableName;


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

            return strtr($tableName, $replace);
        }
		// TODO : MAKE THIS METHODS
        public function truncate(){}
        public function delete(){}

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
		public function getDatabase(){
			return $this->database;
		}

		/**
		 * Database setter
		 *
		 * @param Context $database
		 * @return BaseModel Provides fluent interface
		 * @throws InvalidStateException
		 */
		public function setDatabase(Context $database){
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
		public function ref($table, $column){
			return $this->getTable()->related($table)->through($column);
		}
		// <\/editor-fold>
    }
'
                    );
            }
			$servicesArray = [];
            foreach ($this->getTableNamesForCompiler() as $table) {
                // generate model of tables here
                if (!file_exists($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php') || $forceReload === true){
                    $servicesArray[] = $this->getTableNameToClassName($table['name']) . ': App\\Model\\' . $this->getTableNameToClassName($table['name']) . '
';
					$modelFile = fopen($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php', 'w');
                    $modelFileHeader =
'<?php
    namespace App\Model;

    class ' . $this->getTableNameToClassName($table['name']) . ' extends BaseModel{

		/** @var string */
		protected $table = \'' . $table['name'] . '\';

		/** property */

';
                    fputs($modelFile, $modelFileHeader);
                    foreach($this->getColumnNamesInGivenTable($table['name']) as $column) {
                        fputs($modelFile,
'       /**' . (($column['primary'] === TRUE)? '
        *   @primary TRUE' : '')  . '
        */
        public $' . $column['vendor']['Field'] . ';
');
                    }
                    fputs($modelFile,
'

		/** end property */

');
                    // generate native column methods
                    foreach($this->getColumnNamesInGivenTable($table['name']) as $column){
                        fputs($modelFile,
'
        /**
        *   @return ' . $this->translateReturnValueOfColumn($column['nativetype']) . '
        */
        public function get' . $this->getColumNameToMethodName($column['vendor']['Field']) . '(){
            return $this->' . $column['vendor']['Field'] . ';
        }' . $this->getColumnForeignRelationsMethods($column) . '
');
                    }
                    fputs($modelFile,
'	}

');
				}
			}
			@chmod($this->getConfigDirectory() . '/flarm.neon', 0777);
			$flarmNeonOld = file($this->getConfigDirectory() . '/flarm.neon');
			$flarmNeon = fopen($this->getConfigDirectory() . '/flarm.neon', 'w');
			if(isset($flarmNeonOld)){
				$flarmNeonPuts = false;
				foreach($flarmNeonOld as $value){
					if(trim($value) !== 'services:') $flarmNeonPuts .= $value;
					else break;
				}
				if(strstr(substr($flarmNeonPuts,1), PHP_EOL) !== false)
					fputs($flarmNeon, substr($flarmNeonPuts,0,-1));
				else
					fputs($flarmNeon, $flarmNeonPuts);
			}
			fputs($flarmNeon, '
services:
');
			foreach($servicesArray as $serviceString) {
				fputs($flarmNeon, '	' . $serviceString);
			}
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

    private function getColumNameToMethodName($column){
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

    public function getColumnForeignRelationsMethods($column){
        $chunks = explode('_', $column['vendor']['Field']);
        $method = null;
        if(is_array($chunks) && count($chunks) > 1){
            if(end($chunks) === 'id'){
                // TODO : MAKE RELATION METHOD HERE ! - idea : just inject other model this way into the final object
                $method =
'

        /**
        *   @var $id ' . $this->translateReturnValueOfColumn($column['nativetype']) . '
        *   @return array|null
        */
        public function getRelated' . str_replace('Id', '', $this->getColumNameToMethodName($column['vendor']['Field']))  . '($id){
            $' . lcfirst(str_replace('Id', '', $this->getColumNameToMethodName($column['vendor']['Field']))) . ' = new ' . str_replace('Id', '', $this->getColumNameToMethodName($column['vendor']['Field'])) . 'Model($this->connection);
            return $' . lcfirst(str_replace('Id', '', $this->getColumNameToMethodName($column['vendor']['Field']))) . '->ref(\'' . $this->getRelatedTableNameFromIdColumn($column['vendor']['Field']) . '\', \'' . $column['vendor']['Field'] . '\');
        }
';
            }
        }

        return $method;
    }

    private function getRelatedTableNameFromIdColumn($column){
        return str_replace('_id', '', $column);
    }
}
