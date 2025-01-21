<?php
require_once '../classes/Log.php';
require_once 'config.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

$response = []; // Inicializa a resposta
$log = new log();

try {
    $response = match ($method) {
        'GET' => $log->search($_GET),
        default => methodNotAllowed()
    };
    http_code($method, $response);
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage());
}

// Retornar a resposta como JSON
echo json_encode($response);

// Método não permitido
function methodNotAllowed(): array
{
    http_response_code(405); // Método não permitido
    return ['ok' => false, 'mensagem' => 'Metodo nao suportado'];
}