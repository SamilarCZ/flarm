<?php
	namespace FLaRM;
		
	class FLaRM {
		/**
		 * @var array of Objects
		 */
		protected $objects;
		/**
		 * @var array of all settings
		 */
		protected $settings;

		public function __construct(){
		}

        /**
         * @param $object
         * @param $key
         */
		public function loadObject($object, $key){
			$this->objects[ $key ] = new $object( $this );
		}

        /**
         * @param $object
         * @param $key
         */
		public function forceLoadObject($object, $key){
			$this->objects[ $key ] = unserialize(serialize($object));
		}

        /**
         * @param $setting
         * @param $key
         */
		public function loadSetting($setting, $key){
			$this->settings[ $key ] = $setting;
		}

        /**
         * @param $key
         * @return mixed
         */
		public function getSetting($key){
			return $this->settings[ $key ];
		}

        /**
         * @param $key
         * @return mixed
         */
		public function getObject($key){
			return $this->objects[ $key ];
		}
	}