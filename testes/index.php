<?php

require '../back/classes/Database.php';

$db = Database::getInstance();

var_dump($db->search('usuario',['id' => [1,2,3]]));