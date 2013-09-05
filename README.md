TMDB (tmdb.org) Datasource for CakePHP
=======================

A CakePHP plugin for accessing the TMDB API via a Datasource.

## Plugin purpose ##
The purpose of this plugin is to provide easy access to the TMDB API for any CakePHP application. Currently the plugin only holds the TMDB datasource but will evolve to include Models, Controllers, Views and Helpers. With the plugin you will be able to create an application with access to an extensive list of movies, actors and all the data provided my the TMDB API.

## Requirements ##

* CakePHP version 2.0
* A TMDB API key - To register for an API key, head into your [account page](https://www.themoviedb.org/account/) on The Movie Database (tmdb.org)and generate a new key from within the "API Details" section.

## Installation & Setup##

### Getting the code ###
Via Submodule

	$ cd /your_app_path
	$ git submodule add git@github.com:angelxmoreno/CakePHP-TMDB-Datasource.git Plugin/TmdbApi

Via Clone

	$ cd /your_app_path/Plugin
	$ git git@github.com:angelxmoreno/CakePHP-TMDB-Datasource.git TmdbApi

Via I-don't-use-git-but-I-really-should

	* Unzip this plugin into your app/Plugin/ directory
    * Rename the folder to `TmdbApi`

###Enable plugin
You need to enable the plugin your your_app_path/Config/bootstrap.php file:

	CakePlugin::load('TmdbApi');

If you are already using `CakePlugin::loadAll();`, then this is not necessary.

### Database settings ###
In your your_app_path/Config/database.php add a new entry for the datasource

```php
public $tmdb = array(
	'datasource' => 'TmdbApi.TmdbSource',
	'apiKey' => 'YOUR-API-KEY',
);
```
### Model setup ###
Create a model and  make sure you add the `$useDbConfig` and `$useTable` appropriately. The value of `$useDbConfig` should be the databse.php property that holds your datasource information. The value of `$useTable` pertains to the TMDB entity you will be accessing via the model. Below is an example:

```php
class TmdbMovie extends AppModel {
	public $useDbConfig = 'tmdb';
	public $useTable = 'movies';
}
```

## Usage ##
```php
$results = $this->TmdbMovie->read(null, 550);
debug($results);

$results = $this->TmdbMovie->find('first', array('conditions'=>array(
	'query'=>'Terminator'
)));
debug($results);

$results = $this->TmdbMovie->find('all', array('conditions'=>array(
	'query'=>'Batman'
)));
debug($results);
```


## Support ##
For support, bugs and feature requests, please use the [issues section](https://github.com/angelxmoreno/CakePHP-TMDB-Datasource/issues) of this repository - https://github.com/angelxmoreno/CakePHP-TMDB-Datasource/issues.

## Branch strategy ##
[![Build Status](https://travis-ci.org/angelxmoreno/CakePHP-TMDB-Datasource.png?branch=master)](https://travis-ci.org/angelxmoreno/CakePHP-TMDB-Datasource) The master branch holds the STABLE latest version of the plugin.

[![Build Status](https://travis-ci.org/angelxmoreno/CakePHP-TMDB-Datasource.png?branch=develop)](https://travis-ci.org/angelxmoreno/CakePHP-TMDB-Datasource) Develop branch is UNSTABLE and used to test new features before releasing them.

## Contributing to this Plugin ##
Please feel free to contribute to the plugin with new issues, requests, unit tests, code fixes or new features. If you choose to contribute, create a feature branch from develop, and send me your pull request. Unit tests for new features and issues detected are highly encourged.

## License ##
Copyright 2013 Angel S. Moreno (angelxmoreno). All rights reserved.
Licensed under [The MIT License](http://opensource.org/licenses/mit-license.php).
Redistributions of files must retain the above copyright notice.

## Acknowledgments ##
Thanks to:

[Larry Masters](https://github.com/phpnut) and [everyone](https://github.com/cakephp/cakephp/contributors) who has contributed to [CakePHP](http://cakephp.org).

[Travis Bell](http://blog.travisbell.com/about/), founder and lead developer of The Movie Database ([TMDb](http://www.themoviedb.org/)).
