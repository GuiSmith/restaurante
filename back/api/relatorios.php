<?php
require_once '../classes/Relatorios.php';
require_once 'config.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

$response = []; // Inicializa a resposta
$relatorios = new Relatorios();

try {
    $response = match ($method) {
        'GET' => $relatorios->view($_GET['view'] ?? null),
        default => methodNotAllowed()
    };
    http_code($method, $response);
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage());
}

// Retornar a resposta como JSON
echo json_encode($response);