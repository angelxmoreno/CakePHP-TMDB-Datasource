<?php

App::uses('TmdbApiAppModel', 'TmdbApi.Model');

class TmdbMovie extends TmdbApiAppModel {

    public $findMethods = array(
        'latest' => true,
        'upcoming' => true,
        'now_playing' => true,
        'popular' => true,
        'top_rated' => true,
    );

}
