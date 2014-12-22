<?php

App::uses('AppModel', 'Model');

class TmdbApiAppModel extends AppModel {

	public $useDbConfig = 'tmdb';

	public function __construct($id = false, $table = null, $ds = null) {
		$this->useTable = Inflector::tableize(str_replace('Tmdb', '', get_class($this)));
		parent::__construct($id, $table, $ds);
	}
}
