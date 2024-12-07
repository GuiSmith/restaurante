<?php

require '../../back/classes/Comanda.php';
require '../utils.php';

$comanda = new Comanda();
//echo_var_dump($comanda->all(),'Todas');
//echo_var_dump($comanda->search(['status' => 'ABERTA','id' => '2']),'Abertas');
echo_var_dump($comanda->abrir());