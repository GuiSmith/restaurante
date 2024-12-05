<?php

require_once '../../back/classes/Item.php';
require_once '../utils.php';

$item = new Item();
echo_var_dump($item, 'Instância');
echo_var_dump($item->all(), 'Todos');
$criar_dados = [
    'descricao' => 'coca cola zero',
    'valor' => 4.99,
    'tipo' => 'bebida'
];
//echo_var_dump($criar_dados,'Dados de criação');
//echo_var_dump($item->criar($criar_dados),'Criação');