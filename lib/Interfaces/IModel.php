<?php

    namespace Interfaces;


    interface IModel extends IConditions{
        /**
         * @param $id int|array
         * @return mixed
         */
        public function getByPK($id);

        /**
         * @return mixed
         */
        public function getAll();

        /**
         * @return mixed
         */
        public function getFirst();

        /**
         * @return mixed
         */
        public function getLast();

    }