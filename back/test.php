<?php

require 'classes/Usuario.php';

$usuario = new Usuario();

$data = [
    'email' => 'guilhermessmith2024@gmail.com',
    'senha' => 'Senha123@',
];

var_dump($usuario->login($data));

?>