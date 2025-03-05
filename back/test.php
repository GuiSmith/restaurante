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
 
    var_dump($item->criar($dados));
    echo "\n";

?>