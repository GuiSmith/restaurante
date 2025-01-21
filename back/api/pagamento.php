<?php
require_once '../classes/Pagamento.php';
require_once 'config.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Decodificar o corpo da requisição para métodos POST e PUT
$input = json_decode(file_get_contents('php://input'), true);

$response = []; // Inicializa a resposta
$pagamento = new Pagamento();

try {
    $response = match ($method) {
        'GET' => $pagamento->search($_GET),
        'POST' => $pagamento->criar($input),
        'DELETE' => $pagamento->deletar($_GET),
        default => methodNotAllowed()
    };
    http_code($method, $response);
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage());
}

// Retornar a resposta como JSON
echo json_encode($response);