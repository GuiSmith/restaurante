<?php
    // require 'classes/Comanda.php';
    // require 'classes/Usuario.php';
    // require 'classes/Item.php';
    require 'classes/Log.php';
    // require 'classes/Database.php';

    // $comanda = new Comanda();
    // $usuario = new Usuario();
    // $item = new Item();
    $log = new Log();

    $fields = ['id','uiui','nome','baba','data_hora_cadastro'];
    $conditions = [
        'fields' => implode(',',$fields),
        'limit' => 59,
    ];

    // var_dump($comanda->buscar($conditions));
//     echo "\n\n";
    // var_dump($usuario->buscar($conditions));
    // echo "\n\n";
    // var_dump($item->buscar($conditions));
    // echo "\n\n";
    // var_dump($log->search($conditions));
 
    var_dump($log->search($_GET));
    echo "\n";

?>