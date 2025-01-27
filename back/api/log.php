<?php
require_once 'config.php';
require_once '../functions.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Inicializa a resposta
$response = [];

try {
    // Verificar se usuário está autenticado
    if(!auth()){
        // Não autorizado
        http_response_code(401);
        $response = criar_mensagem(false,'Não autorizado, faça login para continuar');
    }else{
        // Autorizado
        require_once '../classes/Log.php';
        $log = new Log();
        $response = match ($method) {
            'GET' => $log->search($_GET),
            default => methodNotAllowed()
        };
        http_code($method, $response);
    }
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage());
}

// Retornar a resposta como JSON
echo json_encode($response);