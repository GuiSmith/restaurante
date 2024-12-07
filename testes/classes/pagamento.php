<?php

require_once '../../back/classes/Pagamento.php';
require_once '../utils.php';

$pagamento = new Pagamento();
//echo_var_dump($pagamento, 'Instância');
//echo_var_dump($pagamento->all(), 'Todos');
//echo_var_dump($pagamento->search(['id'=>3]));

//Criar pagamento

/*
$dados_pagamento = [
    'id_comanda' => 1,
    'valor' => 89.90,
    'forma_pagamento' => 'cheque'
];
*/

//echo_var_dump($dados_pagamento,'Dados de pagamento');
//echo_var_dump($pagamento->criar($dados_pagamento));

//Deletar pagamento
//echo_var_dump($pagamento->deletar('5'));

echo_var_dump($pagamento->status_comanda(1));