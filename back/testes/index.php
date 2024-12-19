<?php

require '../classes/Database.php';
require 'utils.php';

$db = Database::getInstance();

foreach ([-1,44,55] as $value) {
    $item = $db->search('item',['jinathan'=>$value]);
    echo_var_dump($item);
    if ($item) {
        echo "ok";
    } else {
        echo "Não ok";
    }
}