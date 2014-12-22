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
	    //@todo jobs & genres can only be festch as a list with no ids. Make sure to compensate.
	    'genres' => array('searchable' => false, 'id' => 16, 'listable' => true, 'findable' => false,
		'extraCalls' => array(),
		'schema' => array(
		    'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		    'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    //'jobs' => array('searchable' => false, 'id' => null, 'listable' => true),
	    'movies' => array('searchable' => true, 'id' => 550, 'listable' => false, 'findable' => true,
                'finds' => array(
                    'latest',
                    'upcoming',
                    'now_playing',
                    'popular',
                    'top_rated',
                    ),
		'extraCalls' => array(
		    'alternative_titles',
		    'images',
		    'casts',
		    'keywords',
		    'releases',
		    'trailers',
		    'translations',
		    'similar_movies',
		    'reviews',
		    'lists',
		    'changes',
		),
		'schema' => array(
		    'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		    'adult' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'key' => 'index'),
		    'backdrop_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'budget' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '13,2'),
		    'homepage' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'imdb_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'original_title' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'overview' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'popularity' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '16,13'),
		    'poster_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'release_date' => array('type' => 'date', 'null' => true, 'default' => NULL, 'key' => 'index'),
		    'revenue' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '13,2'),
		    'runtime' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 3),
		    'status' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'tagline' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'title' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'vote_average' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '6,3'),
		    'vote_count' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'adult' => array('column' => 'adult', 'unique' => 0),
			'imdbid' => array('column' => 'imdb_id', 'unique' => 0),
			'release_date' => array('column' => 'release_date', 'unique' => 0),
			'status' => array('column' => 'status', 'unique' => 0),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    'collections' => array('searchable' => true, 'id' => 10, 'listable' => false, 'findable' => false,
		'extraCalls' => array(
		    'images',
		),
		'schema' => array(
		    'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		    'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'overview' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'poster_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'backdrop_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    'people' => array('searchable' => true, 'id' => 287, 'listable' => false, 'findable' => false,
		'extraCalls' => array(
		    'credits',
		    'images',
		    'changes',
		),
		'schema' => array(
		    'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		    'adult' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'key' => 'index'),
		    'biography' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'birthday' => array('type' => 'date', 'null' => true, 'default' => NULL, 'key' => 'index'),
		    'deathday' => array('type' => 'date', 'null' => true, 'default' => NULL, 'key' => 'index'),
		    'homepage' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'imdb_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'place_of_birth' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'popularity' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '16,3'),
		    'profile_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'adult' => array('column' => 'adult', 'unique' => 0),
			'birthday' => array('column' => 'birthday', 'unique' => 0),
			'deathday' => array('column' => 'deathday', 'unique' => 0),
			'imdbid' => array('column' => 'imdb_id', 'unique' => 0),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    'lists' => array('searchable' => true, 'id' => '509ec17b19c2950a0600050d', 'listable' => false, 'findable' => false,
		'extraCalls' => array(),
		'schema' => array(
		    'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'created_by' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'description' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'favorite_count' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		    'item_count' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		    'iso_639_1' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'poster_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'created_by' => array('column' => 'created_by', 'unique' => 0),
			'iso_639_1' => array('column' => 'iso_639_1', 'unique' => 0),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    'companies' => array('searchable' => true, 'id' => 1, 'listable' => false, 'findable' => false,
		'extraCalls' => array(
		    'movies',
		),
		'schema' => array(
		    'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		    'description' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'headquarters' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'homepage' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'logo_path' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'parent_company_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'parent_company_id' => array('column' => 'parent_company_id', 'unique' => 0),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    'keywords' => array('searchable' => true, 'id' => 1721, 'listable' => false, 'findable' => false,
		'extraCalls' => array(),
		'schema' => array(
		    'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		    'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    )),
	    'reviews' => array('searchable' => false, 'id' => '5013bc76760ee372cb00253e', 'listable' => false, 'findable' => false,
		'extraCalls' => array(),
		'schema' => array(
		    'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'author' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'content' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'iso_639_1' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'media_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		    'media_title' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'media_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'url' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		    'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'iso_639_1' => array('column' => 'iso_639_1', 'unique' => 0),
			'media_type' => array('column' => 'media_type', 'unique' => 0),
			'media_id' => array('column' => 'media_id', 'unique' => 0),
		    ),
		    'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
	    ))
	);

	/**
	 * Holds a list of sources (tables) contained in the DataSource
	 *
	 * @var array
	 */
	protected $_sources = array();

	/**
	 * Holds a list of sources (tables) that are searchable by passing query in conditions
	 *
	 * @var array
	 */
	protected $_searchable = array();
        
        /**
	 * Holds a list of sources (tables) that use the endpoints {model}/{endpoint}
	 *
	 * @var array
	 */
	protected $_findable = array();

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
	 * @var array
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
		foreach ($this->_structure as $sourceName => $sourceMeta) {
			//build the $_sources array
			$this->_sources[] = $sourceName;

			//build the $_searchable array
			if ($sourceMeta['searchable']) {
				$this->_searchable[] = $sourceName;
			}
                        
                        //build the $_findable array
			if ($sourceMeta['findable']) {
				$this->_findable[] = $sourceName;
			}

			//build the $_listable array
			if ($sourceMeta['listable']) {
				$this->_listable[] = $sourceName;
			}

			//build the $_descriptions array (schema)
			if ($sourceMeta['schema']) {
				$this->_descriptions[$sourceName] = $sourceMeta['schema'];
			}
		}
		parent::__construct($config);
		$this->_http = new HttpSocket();
		if (!$this->config['apiKey']) {
			throw new MissingDatasourceConfigException(array('config' => 'apiKey'));
		}
		$this->fetchConfiguration();
	}

	/**
	 * Caches/returns cached results for child instances
	 *
	 * @param mixed $data
	 * @return array Array of sources available in this datasource.
	 */
	public function listSources($data = null) {
		return $this->_sources;
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
	 * Get the system wide configuration information
	 * This method currently holds the data relevant to building image URLs as well as the change key map.
	 *
	 * @link http://docs.themoviedb.apiary.io/#get-%2F3%2Fconfiguration
	 * @return TMDb result array
	 */
	public function fetchConfiguration() {
		if (!$this->_tmdbConfig) {
			$cacheKey = ConnectionManager::getSourceName($this) . 'tmdbConfiguration';
			$config = Cache::read($cacheKey, '_cake_model_');
			if (!$config) {
				$config = $this->_request('configuration');
				Cache::write($cacheKey, $config, '_cake_model_');
			}
			$this->_tmdbConfig = $config;
		}
		return $this->_tmdbConfig;
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
		if (isset($queryData['conditions'][$model->alias . '.id'])) {
			$queryData['conditions']['id'] = $queryData['conditions'][$model->alias . '.id'];
			unset($queryData['conditions'][$model->alias . '.id']);
		}
		$this->_checkConditionsKeys($model, $queryData['conditions']);

		//if id is supplied, perform a lookup
		if (array_key_exists('id', $queryData['conditions'])) {
			$lookupResults = $this->lookup($model, $queryData, $recursive);
			$results = array($lookupResults);
		}
                
                //if conditions['query'] is supplied, perform a search
                elseif (array_key_exists('query', $queryData['conditions'])) {
			$searchResults = $this->_search($model, $queryData, $recursive);
			$results = $searchResults['results'];
		} 
                
                //if conditions['find'] is supplied, then fetch {tab;e}/{find}
                elseif (array_key_exists('find', $queryData['conditions'])) {
			$searchResults = $this->_find($model, $queryData, $recursive);
			$results = $searchResults['results'];
		}
                
                else {
			throw new CakeException(__d(
				'tmdb_api', 'Unkown search algorithm for %s (%s). Please use one of the following in your conditions: %s', $model->name, $model->useTable
			));
		}
		foreach ($results as $result) {
			$_results[] = array($model->alias => $result);
		}
		return $_results;
	}

	public function lookup($source, $id, $recursive = null) {
		$params = array();
		$tableName = $this->fullTableName($source);
		if (is_array($id)) {
			$conditions = $id['conditions'];
			$id = $conditions['id'];
			if (isset($conditions['append_to_response']) && is_array($conditions['append_to_response'])) {
				$params['append_to_response'] = implode(',', $conditions['append_to_response']);
			} elseif (isset($conditions['append_to_response']) && $conditions['append_to_response'] === true) {
				$params['append_to_response'] = implode(',', $this->_structure[$tableName]['extraCalls']);
			}
		}
		if ($this->isListable($tableName)) {
			return $this->_listableLookup($tableName, $id);
		} else {
			$path = Inflector::singularize($tableName) . '/' . $id;
			return $this->_request($path, $params);
		}
	}

	protected function _listableLookup($tableName, $id) {
		$list = $this->_buildListable($tableName);
		return array_key_exists($id, $list) ? $list[$id] : false;
	}

	protected function _buildListable($tableName) {
		$cacheKey = ConnectionManager::getSourceName($this) . '_listable_' . $tableName;
		$list = Cache::read($cacheKey, '_cake_model_');
		if (!$list) {
			$path = Inflector::singularize($tableName) . '/' . 'list';
			$rawList = $this->_request($path);
			foreach ($rawList[$tableName] as $row) {
				$list[$row['id']] = $row;
			}
			Cache::write($cacheKey, $list, '_cake_model_');
		}
		return $list;
	}

	public function isListable($tableName) {
		return in_array($tableName, $this->_listable);
	}

	public function isSearchable($source) {
		return in_array($source, $this->_searchable);
	}
        
        public function isFindable($source) {
		return in_array($source, $this->_findable);
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
        
        protected function _find($model, $queryData = array(), $recursive = null) {
		//we assume $queryData['conditions']['query'] exists
		$path = Inflector::singularize($this->fullTableName($model)) . '/' . $queryData['conditions']['find'];
		$results = $this->_request($path);
                if(!array_key_exists('results', $results)){
                    return array('results' => array($results));
                }
                return $results;
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

	protected function _checkConditionsKeys(Model $model, array $conditions) {
		if (!$this->isSearchable($model->useTable) && !array_key_exists('id', $conditions) && !array_key_exists('find', $conditions)) {
			throw new CakeException(__d(
				'tmdb_api', 'Missing required conditions key for %s (%s). You can only search this model by id', $model->name, $model->useTable
			));
		}
                
                if (array_key_exists('find', $conditions) && !$this->isFindable($model->useTable)) {
			throw new CakeException(__d(
				'tmdb_api', '%s (%s) does not have any findable conditions.', $model->name, $model->useTable
			));
		}

                if (array_key_exists('find', $conditions) && $this->isFindable($model->useTable) && !in_array($conditions['find'], $this->_structure[$model->useTable]['finds'])) {
			throw new CakeException(__d(
				'tmdb_api', '%s (%s) does not have any findable method called %s.', $model->name, $model->useTable, $conditions['find']
			));
		}

		if (!array_key_exists('query', $conditions) && !array_key_exists('id', $conditions) && !array_key_exists('find', $conditions)) {
			throw new CakeException(__d(
				'tmdb_api', 'Missing required conditions key for %s (%s). Please use one of the following in your conditions: %s', $model->name, $model->useTable, implode(', ', array('id', 'query', 'find'))
			));
		}
	}

	public function query($url, $params = array(), $method = 'get') {
                if (method_exists($this, $url)) {
                    return call_user_func_array(array($this, $url), $params);
                }
        
		$responseObject = $this->_http->$method($url, $params, $this->_httpRequest);
		$responseBodyJson = $responseObject->body;
		$responseBodyArray = json_decode($responseBodyJson, true);
		if (is_null($responseBodyArray)) {
			$error = json_last_error();
			throw new CakeException($error);
		}
		return $responseBodyArray;
	}
        
        public function popular(){
            return $this->_request('movies/popular');
        }

}
