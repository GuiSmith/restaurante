<?php

    // Configurar cabeçalhos HTTP
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
        http_response_code(200);
        exit();
    }

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
    function get_token()
    {
        // Pega todos os cabeçalhos
        $headers = getallheaders();
        // Verifica se o cabeçalho Authorization existe
        if(isset($headers['Authorization'])){
            // Pega o cabeçalho Authorization
            $header = $headers['Authorization'];
            $parts = explode(' ', $header);
            if(count($parts) == 2){
                return $parts[1];
            }
        }

        // Verifica se o token foi enviado nos cookies
        if(isset($_COOKIE['token'])){
            return $_COOKIE['token'];
        }

        return '';
    }

    // Função que verifica se o token é válido
    function auth(){
        $token = get_token();
        if($token == ''){
            return false;
        }
        require_once '../classes/Usuario.php';
        $usuario = new Usuario();
        return $usuario->token_valido($token);
    }
?>