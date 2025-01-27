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
                'DELETE' => $usuario->deletar($_GET),
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

// Processa requisições POST
// Criar usuário, login e logout
function post(array $data): array
{
    $usuario = new Usuario();
    
    // Verifica se foi informado login e logout ao mesmo tempo
    if(isset($data['login']) && isset($data['logout'])){
        return criar_mensagem(false,'Informe apenas login ou logout', $data);
    }

    // Login
    if(isset($data['login']) && $data['login']){
        return $usuario->login($data);
    }

    // Log out
    if(isset($data['logout']) && $data['logout']){
        return $usuario->logout($data['logout']);
    }

    // Criar usuário
    return $usuario->criar($data);
}