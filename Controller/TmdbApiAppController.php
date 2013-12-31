<?php

App::uses('AppController', 'Controller');

class TmdbApiAppController extends AppController {


	protected function _out($title, $array){
		echo '<h2>' . $title . '</h2>';
		debug($array);
		echo '<hr />';
	}
}
