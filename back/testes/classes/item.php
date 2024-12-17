<?php

require_once '../../classes/Item.php';
require_once '../utils.php';

$item = new Item();
//echo_var_dump($item, 'Instância');

echo_var_dump($item->enum_valores('item_tipos'),'Tipos');

$dados = [
    'descricao' => 'test',
    'valor' => 8.99,
    'tipo' => 'BEBIDA'
];

echo_var_dump($item->criar($dados));