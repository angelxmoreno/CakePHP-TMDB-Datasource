<?php

App::uses('TmdbApiAppController', 'TmdbApi.Controller');
App::uses('ConnectionManager', 'Model');
/**
 * @property Mongo $Mongo
 */
class TestController extends TmdbApiAppController {

	public $uses = array('TmdbApi.TmdbMovie', 'Mongo');

	public function index(){
		$this->autoRender = false;
		//return true;
		$conn = ConnectionManager::getDataSource('tmdb');
		$structure = array(
		    //@todo add extended methods that can be called
		    'movies' => array('searchable' => true, 'id' => 550, 'listable' => false),
		    'collections' => array('searchable' => true, 'id' => 10, 'listable' => false),
		    'people' => array('searchable' => true, 'id' => 287, 'listable' => false),
		    'lists' => array('searchable' => true, 'id' => '509ec17b19c2950a0600050d', 'listable' => false),
		    'companies' => array('searchable' => true, 'id' => 1, 'listable' => false),
		    //'genres' => array('searchable' => false, 'id' => null, 'listable' => true),
		    'keywords' => array('searchable' => true, 'id' => 1721, 'listable' => false),
		    //@todo jobs can only be festch as a list with no ids. Make sure to compensate.
		    //'jobs' => array('searchable' => false, 'id' => null, 'listable' => true),
		    'reviews' => array('searchable' => false, 'id' => '5013bc76760ee372cb00253e', 'listable' => false)
		);
		$sources = $conn->listSources();
		//debug($sources);
		foreach($structure as $source => $meta){
			$data = $conn->lookup($source, $meta['id']);
			$data['source'] = $source;
			foreach($data as $key => $val){
				if(is_array($val)){
					unset($data[$key]);
				}
			}
			echo "<h1>$source</h1>";
			debug($data);
			$this->Mongo->setSource($source);
			$this->Mongo->create();
			$this->Mongo->save($data);
			//debug($this->Mongo->read(null, $this->Mongo->id));
			echo "<hr />";
		}
		return true;
	}

	public function test_all(){
		$conn = ConnectionManager::getDataSource('tmdb');
		$sources = $conn->listSources();
		debug($sources);
		$structure = array(
		    //@todo jobs & genres can only be festch as a list with no ids. Make sure to compensate.
		    'genres' => array('searchable' => false, 'id' => 16, 'listable' => true),
		    //'jobs' => array('searchable' => false, 'id' => null, 'listable' => true),
		    'movies' => array('searchable' => true, 'id' => 550, 'listable' => false),
		    'collections' => array('searchable' => true, 'id' => 10, 'listable' => false),
		    'people' => array('searchable' => true, 'id' => 287, 'listable' => false),
		    'lists' => array('searchable' => true, 'id' => '509ec17b19c2950a0600050d', 'listable' => false),
		    'companies' => array('searchable' => true, 'id' => 1, 'listable' => false),
		    'keywords' => array('searchable' => true, 'id' => 1721, 'listable' => false),
		    'reviews' => array('searchable' => false, 'id' => '5013bc76760ee372cb00253e', 'listable' => false)
		);
		foreach ($sources as $source) {
			//load model
			$modelName = 'TmdbApi.Tmdb' . Inflector::classify($source);
			$model = ClassRegistry::init($modelName);
			$this->_out(
				'Now testing '.$modelName,
				$model->find('first', array('conditions'=>array(
				    'id' => $structure[$source]['id'],
				    'append_to_response' => true
				)))
			);

		}
	}
	public function findid($type = 'Movie', $id = 550) {
		$this->autoRender = false;
		$modelName = 'TmdbApi.Tmdb' . $type;
		$model = ClassRegistry::init($modelName);
		//debug($model->schema(true));

		$findFirstId = $model->find('first', array(
		    'conditions' => array(
			'id' => $id,
			'append_to_response' => true,
		    ),
			));
		$this->_out('find first ' . $type . ' by id', $findFirstId);


		$findAllId = $model->find('all', array(
		    'conditions' => array(
			'id' => $id
		    ),
			));
		$this->_out('find all ' . $type . ' by id', $findAllId);


	}

	public function findqu($type = 'Movie', $query = 'terminator') {
		//$this->autoRender = false;
		$modelName = 'TmdbApi.Tmdb' . $type;
		$model = ClassRegistry::init($modelName);


		try {
			$findFirstQuery = $model->find('first', array(
			    'conditions' => array(
				'query' => $query,
			    ),
				));
			$this->_out('find first ' . $type . ' with query', $findFirstQuery);
		} catch (Exception $e) {
			$this->_out('Tried to search by query but got:' . $e->getMessage());
		}


		try {
			$findAllQuery = $model->find('all', array(
			    'conditions' => array(
				'query' => $query,
			    ),
				));
			$this->_out('find all ' . $type . ' query', $findAllQuery);
		} catch (Exception $e) {
			$this->_out('Tried to search by query but got:' . $e->getMessage());
		}
	}

	protected function _out($title, $array=null) {
		echo '<h2>' . $title . '</h2>';
		if($array) debug($array);
		echo '<hr />';
	}
}
