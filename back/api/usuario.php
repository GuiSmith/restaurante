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
$usuario = new Usuario();

try {
    $response = match ($method) {
        'GET' => $usuario->search($_GET),
        'POST' => post( $input),
        'PUT' => $usuario->atualizar($input),
        'DELETE' => delete(),
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
    $response = criar_mensagem(false,'Erro interno: '.$e->getMessage());
}

// Retornar a resposta como JSON
echo json_encode($response);

// Funções para cada método HTTP

// Processa requisições POST.
//CRIAR ou LOGIN
function post(array $data): array
{
    $usuario = new Usuario();
    if(isset($data['login']) && $data['login']){
        return $usuario->login($data);
    }else{
        return $usuario->criar($data);
    }
}

// Processa requisições DELETE
//DELETAR ou LOGOUT
function delete()
{
    $usuario_obj = new Usuario();

    //Log out
    if(isset($_GET['token'])){
        return $usuario_obj->logout($_GET['token']);
    }

    //Deletar usuário
    if (isset($_GET['id'])) {
        return $usuario_obj->deletar($_GET);
    }

    return criar_mensagem(false,'Informe um token para realizar logout e um id para deletar usuarios');
}

// Método não permitido
function methodNotAllowed(): array
{
    http_response_code(405); // Método não permitido
    return criar_mensagem(false, 'Metodo nao suportado');
}
