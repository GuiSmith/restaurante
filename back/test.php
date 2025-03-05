<?php
    require 'classes/Item.php';

    $item = new Item();

    $dados = [
        'id' => '',
        'ativo' => '',
        'descricao' => 'teste',
        'valor' => 1,
        'tipo' => 'BEBIDA',
        'data_hora_cadastro' => ''
    ];

    $busca = [
        'ativo' => true,
        'fields' => 'id,descricao',
        // 'order_by' => ['id' => 'asc']
    ];
 
    // var_dump($item->criar($dados));
    print_r($item->buscar($busca));

    echo "\n";

?>