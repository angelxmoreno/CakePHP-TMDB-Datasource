<?php
/**
 * TMDB Datasource - A CakePHP datasource for the TMDB (themoviedb.org) V3 API
 *
 * API Documentation: http://docs.themoviedb.apiary.io/
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author	  Angel S. Moreno (aka angelxmoreno)
 * @link          https://github.com/angelxmoreno/CakePHP-TMDB-Datasource
 * @package       datasources
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('HttpSocket', 'Network/Http');

class TmdbSource extends DataSource {

	/**
	 * An optional description of the datasource
	 */
	public $description = 'A CakePHP datasource for the TMDB (themoviedb.org) V3 API';

	/**
	 * Our default config options. These options will be customized in our
	 * ``app/Config/database.php`` and will be merged in the ``__construct()``.
	 */
	public $config = array(
	    //@todo should throw exception if null
	    'apiKey' => null,
	    //@todo get an array of valid languages codes for error checking and default to english
	    'lang' => 'en',
	    //@todo secure should switch the protocol from http to https
	    'secure' => false,
	    'cache' => true,
	);

	/**
	 * Holds a list of sources (tables) contained in the DataSource and other
	 * meta data.
	 *
	 * @var array
	 */
	protected $_structure = array(
	    //@todo add extended methods that can be called
	    'movies' => array('searchable' => true, 'id' => 550, 'listable' => false),
	    'collections' => array('searchable' => true, 'id' => null, 'listable' => false),
	    'people' => array('searchable' => true, 'id' => 287, 'listable' => false),
	    'lists' => array('searchable' => true, 'id' => 74643, 'listable' => false),
	    'companies' => array('searchable' => true, 'id' => 1, 'listable' => false),
	    'genres' => array('searchable' => false, 'id' => null, 'listable' => true),
	    'keywords' => array('searchable' => true, 'id' => 1721, 'listable' => false),
	    //@todo jobs can only be festch as a list with no ids. Make sure to compensate.
	    'jobs' => array('searchable' => false, 'id' => null, 'listable' => true),
	    'reviews' => array('searchable' => false, 'id' => 49026, 'listable' => false)
	);

	/**
	 * Holds a list of sources (tables) contained in the DataSource
	 *
	 * @var array
	 */
	protected $_sources = array();

	/**
	 * Holds a list of sources (tables) that aresearchable by passing query in conditions
	 *
	 * @var array
	 */
	protected $_searchable = array();

	/**
	 * Holds references to descriptions loaded by the DataSource
	 *
	 * @var array
	 */
	protected $_descriptions = array();

	/**
	 * Holds a list of sources (tables) that can fetch lists
	 *
	 * @var array
	 */
	protected $_listable = array();

	/**
	 * The url used to communicate with the API
	 *
	 * @var string
	 */
	protected $_baseUrl = 'http://api.themoviedb.org/3/';

	/**
	 * The system wide configuration information
	 *
	 */
	protected $_tmdbConfig = array();

	/**
	 * HttpSocket
	 *
	 * @var HttpSocket
	 */
	protected $_http;

	/**
	 * Default request array for HttpSocket
	 *
	 * @var array
	 */
	protected $_httpRequest = array(
	    'header' => array(
		'User-Agent' => 'CakePHP TMDB API',
		'accept' => 'application/json',
	    ),
	);

	/**
	 * Create our HttpSocket and handle any config tweaks.
	 */
	public function __construct($config) {
		parent::__construct($config);
		$this->_http = new HttpSocket();
		foreach ($this->_structure as $sourceName => $sourceMeta) {
			//build the $_sources array
			$this->_sources[] = $sourceName;

			//build the $_searchable array
			if ($sourceMeta['searchable']) {
				$this->_searchable[] = $sourceName;
			}

			//build the $_listable array
			if ($sourceMeta['listable']) {
				$this->_listable[] = $sourceName;
			}
		}
		$this->fetchConfiguration();
		debug($this->_tmdbConfig);
	}

	/**
	 * Cache the DataSource description
	 *
	 * @param string $tableName The name of the object (model) to cache
	 * @param mixed $schema The description of the model, usually a string or array
	 * @return mixed
	 */
	protected function _cacheDescription($tableName, $schema = null) {
		if ($this->cacheSources === false) {
			return null;
		}

		if ($schema !== null) {
			return $this->_descriptions[$object] = & $schema;
		}
		$cacheKey = ConnectionManager::getSourceName($this) . '_' . $tableName . '_schema';
		$schema = Cache::read($cacheKey, '_cake_model_');
		if (!$schema) {
			$result = $this->_lookup($tableName, array('conditions' => array('id' => $this->_structure[$tableName]['id'])));
			$schema = array();
			foreach ($result as $key => $val) {
				if (!is_array($val)) {
					$schema[$key] = array(
					    'type' => gettype($val),
					    'null' => true,
					    'key' => null,
					    'length' => null,
					    'default' => null,
					    'key' => null,
					    'collate' => 'utf8_general_ci',
					    'charset' => 'utf8'
					);
				}
			}
			Cache::write($cacheKey, $schema, '_cake_model_');
		}
		return $schema;
	}

	public function fullTableName($model) {
		if ($model instanceof Model) {
			return $model->useTable;
		}
		if (is_string($model)) {
			return $model;
		}
		throw new CakeException(__d('tmdb_api', 'Unable to get table name.'));
	}

	/**
	 * calculate() is for determining how we will count the records and is
	 * required to get ``update()`` and ``delete()`` to work.
	 *
	 * We don't count the records here but return a string to be passed to
	 * ``read()`` which will do the actual counting. The easiest way is to just
	 * return the string 'COUNT' and check for it in ``read()`` where
	 * ``$data['fields'] === 'COUNT'``.
	 */
	public function calculate(Model $model, $func, $params = array()) {
		return 'COUNT';
	}

	/**
	 * Implement the R in CRUD. Calls to ``Model::find()`` arrive here.
	 */
	public function read(Model $model, $queryData = array(), $recursive = null) {
		/**
		 * Here we do the actual count as instructed by our calculate()
		 * method above. We could either check the remote source or some
		 * other way to get the record count. Here we'll simply return 1 so
		 * ``update()`` and ``delete()`` will assume the record exists.
		 */
		if ($queryData['fields'] === 'COUNT') {
			return array(array(array('count' => 1)));
		}
		// check if the conditions in $queryData supply at least one of the required keys
		$queryData['conditions'] = (array) $queryData['conditions'];
		if(isset($queryData['conditions'][$model->alias.'.id'])){
			$queryData['conditions']['id'] = $queryData['conditions'][$model->alias.'.id'];
			unset($queryData['conditions'][$model->alias.'.id']);
		}
		$this->_checkConditionsKeys($model, $queryData['conditions']);

		//if id is supplied, perform a lookup
		if (array_key_exists('id', $queryData['conditions'])) {
			$lookupResults = $this->_lookup($model, $queryData, $recursive);
			$results = array($lookupResults);
		} elseif (array_key_exists('query', $queryData['conditions'])) {
			$searchResults = $this->_search($model, $queryData, $recursive);
			$results = $searchResults['results'];
		} else {
			throw new CakeException(__d(
				'tmdb_api', 'Unkown search algorithm for %s (%s). Please use one of the following in your conditions: %s', $model->name, $model->useTable
			));
		}
		foreach ($results as $result) {
			$_results[] = array($model->alias => $result);
		}
		return $_results;
	}

	protected function _lookup($model, $queryData, $recursive = null) {
		//we assume $queryData['conditions']['id'] exists
		$tableName = $this->fullTableName($model);
		$path = Inflector::singularize($tableName) . '/' . $queryData['conditions']['id'];
		return $this->_request($path);
	}

	protected function _search($model, $queryData = array(), $recursive = null) {
		//we assume $queryData['conditions']['query'] exists
		$tableName = $this->fullTableName($model);
		$path = 'search' . '/' . Inflector::singularize($tableName);
		$params = array(
		    'query' => $queryData['conditions']['query'],
		);
		return $this->_request($path, $params);
	}

	/**
	 * Implement the C in CRUD. Calls to ``Model::save()`` without $model->id
	 * set arrive here.
	 */
	public function create(Model $model, $fields = null, $values = null) {
		throw new CakeException(__d('tmdb_api', 'Not yet Implemented'));
	}

	/**
	 * Implement the U in CRUD. Calls to ``Model::save()`` with $Model->id
	 * set arrive here. Depending on the remote source you can just call
	 * ``$this->create()``.
	 */
	public function update(Model $model, $fields = null, $values = null, $conditions = null) {
		throw new CakeException(__d('tmdb_api', 'Not yet Implemented'));
	}

	/**
	 * Implement the D in CRUD. Calls to ``Model::delete()`` arrive here.
	 */
	public function delete(Model $model, $id = null) {
		throw new CakeException(__d('tmdb_api', 'Not yet Implemented'));
	}

	protected function _request($path, $params = array(), $method = 'GET') {
		$method = strtolower($method);
		$params['api_key'] = $this->config['apiKey'];
		$url = $this->_baseUrl . trim($path, '/');
		$this->_log($method, $url, $params);
		$response = $this->query($url, $params, $method);
		if (isset($response['status_message'])) {
			throw new CakeException($response['status_message']);
		}
		return $response;
	}

	protected function _log($method, $path, $params) {
		$this->_requestLogs[] = array_combine(array('method', 'path', 'params'), func_get_args());
	}

	protected function _isSearchable($source) {
		return in_array($source, $this->_searchable);
	}

	protected function _checkConditionsKeys(Model $model, array $conditions) {
		if (!$this->_isSearchable($model->useTable) && !array_key_exists('id', $conditions)) {
			throw new CakeException(__d(
				'tmdb_api', 'Missing required conditions key for %s (%s). You can only search this model by id', $model->name, $model->useTable
			));
		}

		if (!array_key_exists('query', $conditions) && !array_key_exists('id', $conditions)) {
			throw new CakeException(__d(
				'tmdb_api', 'Missing required conditions key for %s (%s). Please use one of the following in your conditions: %s', $model->name, $model->useTable, implode(', ', array('id', 'query'))
			));
		}
	}

	/**
	 * Get the system wide configuration information
	 * This method currently holds the data relevant to building image URLs as well as the change key map.
	 *
	 * @link http://docs.themoviedb.apiary.io/#get-%2F3%2Fconfiguration
	 * @return TMDb result array
	 */
	public function fetchConfiguration() {
		if(!$this->_tmdbConfig){
			$cacheKey = ConnectionManager::getSourceName($this) . 'tmdbConfiguration';
			$config = Cache::read($cacheKey, '_cake_model_');
			if (!$config) {
				$config = $this->query($this->_baseUrl . 'configuration', array('api_key' => $this->config['apiKey']), 'get');
				Cache::write($cacheKey, $config, '_cake_model_');
			}
			$this->_tmdbConfig = $config;
		}
		return $this->_tmdbConfig;
	}

	public function query($url, $params = array(), $method = 'get') {
		$responseObject = $this->_http->$method($url, $params, $this->_httpRequest);
		$responseBodyJson = $responseObject->body;
		$responseBodyArray = json_decode($responseBodyJson, true);
		if (is_null($responseBodyArray)) {
			$error = json_last_error();
			throw new CakeException($error);
		}
		return $responseBodyArray;
	}
}
