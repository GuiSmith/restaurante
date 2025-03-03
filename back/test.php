<?php
    require 'functions.php';

    $dados = [
        'nome' => 'gui',
        'idade' => 18,
        'ano_cadastro' => '2024'
    ];

    $dados_permitidos = ['nome','idade'];

    var_dump(array_keys_filter($dados,$dados_permitidos));
    

    echo "\n";

?>