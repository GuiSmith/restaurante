<?php

require_once 'CRUDModel.php';

class Usuario extends CRUDModel
{
    protected static $table = 'usuario';
    
    public function __construct($debug = false)
    {
        parent::__construct(debug: $debug);
        if ($debug)
            echo "<p>Construtor de usuario</p>";
    }

    // Criar usuário
    public function criar(array $data): array
    {
        // Verificar dados obrigatórios
        $dados_obrigatorios = ['nome', 'email', 'senha'];
        if(!array_keys_exists($data,$dados_obrigatorios)){
            return ['ok' => false, 'mensagem' => "Informe os campos obrigatorios: ".implode(',',$dados_obrigatorios)];
        }

        // Nome
        if (empty($data['nome'])) {
            return ['ok' => false, 'mensagem' => 'Nome vazio, informe um nome valido'];
        }

        // E-mail válido
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mensagem' => 'E-mail invalido'];
        }

        // E-mail disponível
        if (self::email_existe($data['email'])) {
            return ['ok' => false, 'mensagem' => 'E-mail ja cadastrado'];
        }

        // Senha válida
        if (empty($data['senha']) || strlen($data['senha']) < 6) {
            return ['ok' => false, 'mensagem' => 'Senha deve ter pelo menos 6 caracteres'];
        }

        // Criptografar senha
        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);

        // Inserir usuário no banco
        try {
            $id = $this->insert($data);
            return ['ok' => true, 'mensagem' => 'Usuario criado com sucesso', 'id' => $id];
        } catch (Exception $e) {
            return ['ok' => false, 'mensagem' => $e->getMessage()];
        }
    }

    // Atualizar usuário
    public function atualizar(array $data): array
    {
        // ID obrigatório
        if (!isset($data['id'])) {
            return ['ok' => false, 'mensagem' => 'ID e obrigatório'];
        }

        // Nome válido
        if (isset($data['nome']) && empty($data['nome'])) {
            return ['ok' => false, 'mensagem' => 'Nome vazio, informe um nome valido'];
        }

        // E-mail válido
        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'mensagem' => 'E-mail invalido'];
            }

            // Verificar se o e-mail pertence a outro usuário
            $id_do_email_buscado = $this->search(['email' => $data['email']], ['id']);
            if (!empty($id_do_email_buscado) && $id_do_email_buscado[0]['id'] != $data['id']) {
                return ['ok' => false, 'mensagem' => 'E-mail ja está sendo usado por outro usuario'];
            }
        }

        // Atualizar no banco
        try {
            $linhas_afetadas = $this->update($data);
            return ['ok' => true, 'mensagem' => "usuario atualizado com sucesso", 'rows' => $linhas_afetadas];
        } catch (Exception $e) {
            return ['ok' => false, 'mensagem' => $e->getMessage()];
        }
    }

    // Deletar usuário
    public function deletar($id_string): array
    {
        // ID obrigatório
        if (empty($id_string)) {
            return ['ok' => false, 'mensagem' => 'ID e necessario para deletar'];
        }

        // Deletar no banco
        try {
            $ids = explode($id_string);
            $linhas_afetadas = $this->delete($ids);
            return [
                'ok' => true,
                'mensagem' => "$linhas_afetadas usuarios deletados com sucesso"
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'mensagem' => $e->getMessage()];
        }
    }

    // Verificar se um email já existe no banco
    public function email_existe($email): bool
    {
        return !empty($this->search(['email' => $email], ['id']));
    }
    // Função de login
    // Função de login
    public function login(array $data): array
    {
        // Login com token
        if (isset($data['token'])) {
            $usuario = $this->buscar_por_token($data['token'], ['id','email','senha','token','data_hora_expiracao_token']);
            if (!$usuario) {
                return ['ok' => false, 'mensagem' => 'Token não encontrado, realize login com email e senha'];
            }
            if ($this->token_expirado($usuario['data_hora_expiracao_token'])) {
                $this->inutilizar_token($data['token'], $usuario['id']);
                return ['ok' => false, 'mensagem' => 'Token expirado, realize login com email e senha'];
            }
        }
        // Login com email e senha
        else {
            if (!isset($data['email']) || empty($data['senha'])) {
                return ['ok' => false, 'mensagem' => 'E-mail e senha são obrigatórios'];
            }

            $usuario = $this->search(['email' => $data['email']], ['id','email','senha','token','data_hora_expiracao_token'])[0] ?? null;
            if (!$usuario || !password_verify($data['senha'], $usuario['senha'])) {
                return ['ok' => false, 'mensagem' => 'Credenciais invalidas'];
            }
        }

        // Atualiza ou gera um token
        if (empty($usuario['token']) || $this->token_expirado($usuario['data_hora_expiracao_token'])) {
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+1 year'));
            $this->atualizar_token($usuario['id'], $token, $expiracao);
        } else {
            $token = $usuario['token'];
            $expiracao = $usuario['data_hora_expiracao_token'];
        }

        return ['ok' => true, 'mensagem' => 'Login realizado com sucesso', 'token' => $token, 'data_hora_expiracao_token' => $expiracao];
    }

    // Função de logout
    public function logout(string $token): array
    {
        $usuario = $this->buscar_por_token($token,['id','token']);
        if (!$usuario) {
            return ['ok' => false, 'mensagem' => 'Token nao encontrado'];
        }
        $token_query = $this->inutilizar_token($token, $usuario['id']);
        if($token_query->rowCount() > 0){
            return ['ok' => true, 'mensagem' => 'Log out realizado com sucesso!'];
        }else{
            return ['ok' => false, 'mensagem' => 'Log out nao pode ser realizado, token nao foi inutilizado'];
        }
    }

    // Função para buscar usuário por token
    public function buscar_por_token(string $token, array $fields =[]): ?array
    {
        if(!empty($fields) && !in_array('data_hora_expiracao_token',$fields)){
            $fields[] = 'data_hora_expiracao_token';
        }
        $usuario = $this->search(['token' => $token], $fields);
        $usuario = $usuario[0] ?? null;
        if($usuario != null){
            if($this->token_expirado($usuario['data_hora_expiracao_token'])){
                return ['ok' => false,'mensagem' => 'token expirado'];
            }
        }
        return $usuario;
    }

    // Função para verificar se o token está expirado
    protected function token_expirado($dataExpiracao): bool
    {
        return strtotime($dataExpiracao) < strtotime('now');
    }

    // Função para atualizar o token
    private function atualizar_token(int $id, string $token, string $expiracao): void
    {
        $this->update(
            ['token' => $token, 'data_hora_expiracao_token' => $expiracao, 'id' => $id]
        );
    }

    // Função para inutilizar um token
    private function inutilizar_token(string $token, $id)
    {
        $query = $this->update(
            ['token' => null, 'data_hora_expiracao_token' => null, 'id' => $id]
        );
        return $query;
    }
}