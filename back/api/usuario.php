<?php
require_once '../classes/Usuario.php';

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
$usuario_obj = new Usuario();

try {
    $response = match ($method) {
        'GET' => get(),
        'POST' => post($input),
        'PUT' => put($input),
        'DELETE' => delete($input),
        default => methodNotAllowed()
    };
} catch (Exception $e) {
    http_response_code(500); // Erro interno do servidor
    $response = ['ok' => false, 'mensagem' => 'Erro interno: ' . $e->getMessage()];
}

// Retornar a resposta como JSON
echo json_encode($response);

// Funções para cada método HTTP

// Processa requisições GET
function get(): array
{
    global $usuario_obj;
    if (isset($_GET['id'])) {
        // Buscar usuário por ID
        return $usuario_obj->find((int)$_GET['id']);
    } elseif (!empty($_GET)) {
        // Buscar usuários com filtros (search)
        return $usuario_obj->search($_GET);
    } else {
        // Listar todos os usuários
        return ['ok' => true, 'dados' => $usuario_obj->all()];
    }
}

// Processa requisições POST
function post(array $data): array
{
    global $usuario_obj;
    if (empty($data)) {
        return ['ok' => false, 'mensagem' => 'Dados ausentes para criar o usuário'];
    }
    return $usuario_obj->criar($data);
}

// Processa requisições PUT
function put(array $data): array
{
    global $usuario_obj;
    if (empty($data) || !isset($data['id'])) {
        return ['ok' => false, 'mensagem' => 'ID e dados são obrigatórios para atualizar'];
    }
    return $usuario_obj->atualizar($data);
}

// Processa requisições DELETE
function delete(array $data): array
{
    global $usuario_obj;
    if (!isset($data['ids'])) {
        return ['ok' => false, 'mensagem' => 'IDs são obrigatórios para deletar'];
    }
    return $usuario_obj->deletar($data['ids']);
}

// Método não permitido
function methodNotAllowed(): array
{
    http_response_code(405); // Método não permitido
    return ['ok' => false, 'mensagem' => 'Método não suportado'];
}
