<?php

App::uses('AppModel', 'Model');

class TmdbApiAppModel extends AppModel {

	public $useDbConfig = 'tmdb';

	public function __construct($id = false, $table = null, $ds = null) {
		$this->useTable = Inflector::tableize(str_replace('Tmdb', '', get_class($this)));
		parent::__construct($id, $table, $ds);
	}
        
        public function __call($method, $params) {
            if($this->_isMagicFind($method)){
                array_unshift($params, $this->_magicFindType($method));
                return call_user_func_array(array($this, '_magicFind'), $params);
            }
            return parent::__call($method, $params);
        }
        
        protected function _isMagicFind($method){
            return (bool) array_key_exists($this->_magicFindType($method), $this->findMethods);
        }
        
        protected function _magicFindType($method){
            return str_replace('_find', '', strtolower($method));
        }
        
        protected function _magicFind($type, $state, $query, $results = array()) {
            if ($state === 'before') {
                $query['conditions']['find'] = $type;
                return $query;
            }
            return $results;
        }
}
