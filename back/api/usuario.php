<?php
require_once '../classes/Usuario.php';
require_once 'config.php';

// Obter o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Decodificar o corpo da requisição para métodos POST e PUT
$input = json_decode(file_get_contents('php://input'), true);

$response = []; // Inicializa a resposta
$usuario = new Usuario();

try {
    // Verifica o que fazer de acordo com o método HTTP
    if ($method === 'POST' && get_token() == '') {
        // Login ou criar usuário
        $response = post($input);
    }else{
        // Verificar se usuário está autenticado
        if(!auth()){
            // Não autorizado
            http_response_code(401);
            $response = criar_mensagem(false,'Não autorizado, faça login para continuar');
        }else{
            // Autorizado
            $input['token'] = get_token();
            $response = match ($method) {
                'POST' => post($input),
                'GET' => $usuario->search($_GET),
                'PUT' => $usuario->atualizar($input),
                'DELETE' => delete(),
                default => methodNotAllowed()
            };
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
