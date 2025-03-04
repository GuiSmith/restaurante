<?php
    require 'classes/Comanda.php';
    require 'classes/Usuario.php';
    require 'classes/Item.php';
    // require 'classes/Database.php';

    $comanda = new Comanda();
    $usuario = new Usuario();
    $item = new Item();

    $fields = ['id','uiui','nome','baba','data_hora_cadastro'];
    $conditions = [
        'ativo' => true,
        'fields' => implode(',',$fields),
        'limit' => 1,
    ];

    var_dump($comanda->buscar($conditions));
    echo "\n\n";
    var_dump($usuario->buscar($conditions));
    echo "\n\n";
    var_dump($item->buscar($conditions));
    
    echo "\n";

?>