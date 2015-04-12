<?php

    namespace ActiveRecord;

    use Interfaces\IModel;

    class Settings extends Helper implements IModel{

        # explicit table name
        static $table_name = 'settings';

        # explicit pk since our pk is not "id"
        static $primary_key = 'id';

        # explicit connection name since we always want our test db with this model
        static $connection = 'development';

        # explicit database name will generate sql like so => my_db.my_book
        static $db = 'kap2';

        /**
         * @var int
         */
        public $id;
        /**
         * @var mixed
         */
        public $key;
        /**
         * @var mixed
         */
        public $value;

        public $where;
        public $limit;
        public $order;
        public $groupBy;

        public function __construct($table_name){
            $this->where = '';
            $this->limit = '';
            $this->order = '';
            $this->groupBy = '';
        }


        /**
         * @param array|int $id
         * @return mixed
         * @throws RecordNotFound
         */
        public function getByPK($id){
            return Settings::find($id);
        }

        /**
         * @return array
         */
        public function getAll(){
            return Settings::all();
        }

        /**
         * @return Model
         */
        public function getFirst(){
            return Settings::first();
        }

        /**
         * @return Model
         */
        public function getLast(){
            return Settings::last();
        }

    }