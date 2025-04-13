<?php

require 'classes/Relatorios.php';

$relatorio = new Relatorios();

var_dump($relatorio->view('ordens_producao'));

?>