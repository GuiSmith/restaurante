<?php

require_once '../../back/classes/Item.php';
require_once '../utils.php';

$item = new Item();
//echo_var_dump($item, 'Instância');
//echo_var_dump($item->all(), 'Todos');
echo_var_dump($item->search(['id'=>3]));
/*
//Criar item
$criar_dados = [
    'descricao' => 'coca cola 600ML',
    'valor' => 6.99,
    'tipo' => 'bebida'
];
echo_var_dump($criar_dados,'Dados de criação');
echo_var_dump($item->criar($criar_dados),'Criação');
*/

/*
//Atualizar item
$atualizar_dados = [
    'id' => 5,
    'descricao' => 'coca cola 600 ml',
    'valor' => 7.5,
    'tipo' => 'bebida'
];
echo_var_dump($item->atualizar($atualizar_dados),'Atualização');
*/

//Deletar item
//echo_var_dump($item->deletar('5'));