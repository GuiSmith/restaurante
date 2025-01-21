<?php
require_once '../classes/Item.php';
require_once 'config.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Decodificar o corpo da requisição para métodos POST e PUT
$input = json_decode(file_get_contents('php://input'), true);

$response = []; // Inicializa a resposta
$item = new Item();

try {
    $response = match ($method) {
        'GET' => $item->search($_GET),
        'POST' => $item->criar($input),
        'PUT' => $item->atualizar($input),
        'DELETE' => $item->deletar($_GET),
        default => methodNotAllowed()
    };
    http_code($method, $response);
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage(),['GET' => $_GET, 'POST' => $_POST]);
}

// Retornar a resposta como JSON
echo json_encode($response);
