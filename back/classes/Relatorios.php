<?php

require_once 'Database.php';
require_once __DIR__."/../functions.php";

class Relatorios {
    private static $instance = null;
    private static $db;

    function __construct(){
        static::$db = Database::getInstance();
    }

    public function view($view = 'ordens_producao')
    {

        return $view ? self::$db->fetchAll("SELECT * FROM $view") : [];
    }

}