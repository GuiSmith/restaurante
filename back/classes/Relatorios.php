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
        if ($view){
            $sql = "SELECT * FROM $view";
        }else{
            $sql = "SELECT viewname as view FROM pg_catalog.pg_views WHERE schemaname = 'public'";
        }
        return self::$db->fetchAll($sql);
    }

}