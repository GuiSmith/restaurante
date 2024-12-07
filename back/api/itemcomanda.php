<?php
require_once '../classes/ItemComanda.php';

// Configurar cabeçalhos HTTP
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Decodificar o corpo da requisição para métodos POST e PUT
$input = json_decode(file_get_contents('php://input'), true);

$response = []; // Inicializa a resposta
$itemcomanda = new itemcomanda();

try {
    $response = match ($method) {
        'GET' => $itemcomanda->search($_GET),
        'POST' => $itemcomanda->criar($input),
        'PUT' => $itemcomanda->atualizar($input),
        'DELETE' => $itemcomanda->deletar($_GET),
        default => methodNotAllowed()
    };
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