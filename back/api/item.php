<?php
require_once '../classes/Item.php';

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
$item = new Item();

try {
    $response = match ($method) {
        'GET' => $item->search($_GET),
        'POST' => $item->criar($input),
        'PUT' => $item->atualizar($input),
        'DELETE' => $item->deletar($_GET),
        default => methodNotAllowed()
    };
    if(isset($response['status'])){
        http_response_code($response['status']);
    }else{
        if(isset($response['ok'])){
            http_response_code( $response['ok'] ? 200 : 400);
        }else{
            http_response_code(200);
        }
    }
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage(),['GET' => $_GET, 'POST' => $_POST]);
}

// Retornar a resposta como JSON
echo json_encode($response);

// Método não permitido
function methodNotAllowed(): array
{
    http_response_code(405); // Método não permitido
    return ['ok' => false, 'mensagem' => 'Metodo nao suportado'];
}
