<?php

require '../../back/classes/Comanda.php';
require '../utils.php';

$comanda = new Comanda();
echo_var_dump($comanda->all());
echo_var_dump($comanda->criar('ABERTO'));