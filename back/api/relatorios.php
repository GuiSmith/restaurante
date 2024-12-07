<?php
require_once '../classes/Relatorios.php';

// Configurar cabeçalhos HTTP
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

$response = []; // Inicializa a resposta
$relatorios = new Relatorios();

try {
    $response = match ($method) {
        'GET' => $relatorios->view($_GET['view'] ?? null),
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