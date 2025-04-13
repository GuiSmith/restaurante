<?php

require_once 'Database.php';
require_once __DIR__."/../functions.php";

class Relatorios {
    private static $instance = null;
    private static $db;

    function __construct(){
        static::$db = Database::getInstance();
    }

    public function view(string $view = null)
    {   
        $sql_views = "SELECT viewname as view FROM pg_catalog.pg_views WHERE schemaname = 'public'";
        $views = array_map(function($viewname){
            return $viewname['view'];
        },self::$db->fetchAll($sql_views));
        if(!in_array($view,$views)){
            return $views;
        }else{
            $sql = "SELECT * FROM $view";
            return self::$db->fetchAll($sql);
        }
    }
}