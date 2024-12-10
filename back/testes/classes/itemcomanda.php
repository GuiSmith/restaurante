<?php

require '../../back/classes/ItemComanda.php';
require '../../back/classes/Comanda.php';
require '../utils.php';

$itemcomanda = new ItemComanda();

$item = [
    'id_item' => 1,
    'id_comanda' => 1,
    'quantidade' => 3
];
echo_var_dump($itemcomanda->criar($item));
/*
$item = [
    'id' => 1,
    'isento' => true
];
echo_var_dump($itemcomanda->atualizar($item));
echo_var_dump($itemcomanda->search(['id' => 3])[0]['isento'],'Isento');
$comanda_obj = new Comanda();
$comanda = $comanda_obj->search(['id'=>1])[0];
echo_var_dump($comanda['valor_total'],'Total');
echo_var_dump($comanda['descontos'], 'Descontos');
*/