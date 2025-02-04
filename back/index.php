<?php

// Aqui criarei um usuário para poder fazer login e realizar testes via API
require_once 'classes/Usuario.php';
$usuario = new Usuario();

// Dados usuário
$dados = [
    'nome' => 'Guilherme',
    'email' => 'guilherme.rodrigues@ixcsoft.com.br',
    'senha' => '123456',
];

print_r($dados);
echo "<hr>";

try {
    print_r($usuario->criar($dados));
    echo "<hr>";
    print_r($usuario->login(['email' => $dados['email'],'senha' => $dados['senha']]));
} catch (Exception $e) {
    print_r($e->getMessage());
}

?>