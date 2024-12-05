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
        'POST' => post( $input),
        'PUT' => put($input),
        'DELETE' => delete(),
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
function get()
{
    global $usuario_obj;
    if (isset($_GET['token'])) {
        // Buscar usuário por Token
        return $usuario_obj->buscar_por_token($_GET['token']);
    } elseif (isset($_GET['id'])) {
        // Buscar usuários com ID
        return $usuario_obj->find($_GET['id']);
    } elseif (!empty($_GET)) {
       // Buscar usuários com filtros (search)
       return $usuario_obj->search($_GET);
    }else{
        // Listar todos os usuários
        return ['ok' => true, 'dados' => $usuario_obj->all()];
    }
}

// Processa requisições POST
function post(array $data): array
{
    global $usuario_obj;
    if (empty($data)) {
        return ['ok' => false, 'mensagem' => 'Dados ausentes para criar o usuario'];
    }
    if(isset($data['login']) && $data['login']){
        return $usuario_obj->login($data);
    }else{
        return $usuario_obj->criar($data);
    }
}

// Processa requisições PUT
function put(array $data): array
{
    global $usuario_obj;
    if (empty($data) || !isset($data['id'])) {
        return ['ok' => false, 'mensagem' => 'ID e dados sao obrigatorios para atualizar'];
    }
    return $usuario_obj->atualizar($data);
}

// Processa requisições DELETE
function delete()
{
    global $usuario_obj;

    //Log out
    if(isset($_GET['token'])){
        return $usuario_obj->logout($_GET['token']);
    }

    //Deletar usuário
    if (isset($_GET['id'])) {
        return $usuario_obj->deletar($_GET['id']);
    }

    return ['ok'=> false,'mensagem'=> 'Informe um token para realizar logout e um id para deletar usuarios'];
}

// Método não permitido
function methodNotAllowed(): array
{
    http_response_code(405); // Método não permitido
    return ['ok' => false, 'mensagem' => 'Metodo nao suportado'];
}
