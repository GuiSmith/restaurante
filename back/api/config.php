<?php

    // Configurar cabeçalhos HTTP
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    // Método não permitido
    function methodNotAllowed(): array
    {
        http_response_code(405); // Método não permitido
        return ['ok' => false, 'mensagem' => 'Metodo nao suportado'];
    }

    function http_code($method, $response){
        if(isset($response['status'])){
            http_response_code($response['status']);
        }else{
            switch($method) {
                case 'GET':
                    if(count($response) > 0){
                        http_response_code(200);
                    }else{
                        http_response_code(204);
                    }
                    break;
                case 'POST':
                    if ($response['ok']) {
                        http_response_code(201);
                    } else {
                        http_response_code(400);
                    }
                    break;
                case 'PUT':
                case 'DELETE':
                    if ($response['ok']) {
                        http_response_code(200);
                    } else {
                        http_response_code(400);
                    }
                    break;
                default: 
                    http_response_code(405);
            }
        }

    }

    // Função que recebe o header de autorização e retorna o token
    function get_token($auth_header)
    {
        if (isset($auth_header)) {
            $headers = explode(' ', $auth_header);
            if (count($headers) == 2) {
                return $headers[1];
            }else{
                return '';
            }
        }else{
            return '';
        }
    }

    // Função que verifica se o token é válido
    function auth(){
        // Verificar se o cabeçalho de autorização foi enviado
        if(!isset($_SERVER['HTTP_AUTHORIZATION'])){
            return false;
        }else{
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        }
        require_once '../classes/Usuario.php';
        $token = get_token($auth_header);
        $usuario = new Usuario();
        return $usuario->token_valido($token);
    }
?>