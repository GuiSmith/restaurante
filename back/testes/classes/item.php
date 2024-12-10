<?php

require_once '../../back/classes/Item.php';
require_once '../utils.php';

$item = new Item();
//echo_var_dump($item, 'Instância');
//echo_var_dump($item->all(), 'Todos');
//echo_var_dump($item->search(['id'=>3]));

//Criar item

$itens = [
    [
        'descricao' => 'risoto',
        'valor' => 89.90,
        'tipo' => 'prato',
    ],
    [
        'descricao' => 'espumante 800ml',
        'valor' => 150.00,
        'tipo' => 'bebida'
    ]
];

echo_var_dump($itens,'Dados de criação');
foreach ($itens as $item_local) {
    echo_var_dump($item->criar($item_local),'Criando');
}
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