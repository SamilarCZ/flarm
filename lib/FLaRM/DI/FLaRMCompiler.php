<?php

/**
 * This file is part of the FLaRM Framework (http://flarm.org) using Nette Framework (http://nette.org)
 * Copyright (c) 2015 Filip Lánský (http://filip-lansky.cz)
 */

namespace FLaRM\DI;

use FLaRM;


/**
 * The compiler and generator of model layer of application created from database structure
 *
 * @author     Filip Lánský
 */
class FLaRMCompiler extends FLaRMContainer{
    /**
     * @var FLaRMContainer
     */
    protected $flarmContainer;

    private $connection;
    public function __construct(FLaRMContainer $FLaRMContainer){
        parent::__construct();
        $this->flarmContainer = $FLaRMContainer;
        $this->connection = $this->flarmContainer->netteContainer->getByType('\Nette\Database\Connection');
    }

    public function run($forceReload = false){
        if(isset($this->connection)) {
            if(!file_exists($this->getModelDirectory(). '/BaseModel.php') || $forceReload === true){
                $baseModelFile = fopen($this->getModelDirectory() . '/BaseModel.php', 'w');
                fputs($baseModelFile,
'<?php
    namespace App\Model;

    use Nette;
    use Nette\Database\Connection;

    abstract class BaseModel extends Nette\Object
    {
        /** @var Connection */
        protected $db;

        /** @var string */
        private $tableName;


        /**
         * @param $connection Connection
         */
        public function __construct(Connection $connection)
        {
            $this->db = $connection;
            $this->tableName = $this->tableNameByClass(get_class($this));
        }


        /**
         * Určí tabulku dle názvu třídy
         * @param string
         * @return string
         * @result: Pages => pages, ArticleTag => article_tag
         */
        private function tableNameByClass($className)
        {
            $tableName = explode("\\\\", $className);
            $tableName = lcfirst(array_pop($tableName));

            $replace = array(); // A => _a
            foreach (range("A", "Z") as $letter) {
                $replace[$letter] = "_" . strtolower($letter);
            }

            return strtr($tableName, $replace);
        }

        public function get(){}
        public function getAll(){}
        public function fetch(){}
        public function fetchAll(){}
        public function truncate(){}
        public function update(){}
        public function delete(){}

        // přidáme vlastní metody: insert, update, delete, count,
        // fetchSingle, fetchPairs atd.

    }
'
                    );
            }
            foreach ($this->getTableNamesForCompiler() as $table) {
                dump($table);
                dump($this->getColumnNamesInGivenTable($table['name']));
                // generate model of tables here
                if (!file_exists($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php') || $forceReload === true){
                    $modelFile = fopen($this->getModelDirectory() . '/' . $this->getTableNameToClassName($table['name']) . '.php', 'w');
                    $modelFileHeader =
'<?php
    namespace App\Model;

    class ' . $this->getTableNameToClassName($table['name']) . ' extends BaseModel{

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
'   }

');
                }
//                dump($table);
            }
            return true;
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
        return $this->flarmContainer->parameters['appDir'] . '/../app/model';
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
