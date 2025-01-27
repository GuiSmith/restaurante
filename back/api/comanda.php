<?php
require_once '../classes/Comanda.php';
require_once 'config.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Decodificar o corpo da requisição para métodos POST e PUT
$input = json_decode(file_get_contents('php://input'), true);

$response = []; // Inicializa a resposta
$comanda = new comanda();

try {
    // Verificar autenticação
    if(!auth()){
        http_response_code(401); // Não autorizado
        $response = criar_mensagem(false,'Não autorizado');
    }else{
        $response = match ($method) {
            'GET' => $comanda->search($_GET),
            'POST' => $comanda->abrir($input),
            'PUT' => $comanda->fechar($input),
            'DELETE' => $comanda->deletar($_GET),
            default => methodNotAllowed()
        };
        http_code($method, $response);
    }
    http_code($method, $response);
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage());
}

// Retornar a resposta como JSON
echo json_encode($response);