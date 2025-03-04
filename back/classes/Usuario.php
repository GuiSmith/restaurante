<?php

require_once 'CRUDModel.php';

class Usuario extends CRUDModel
{
    protected static $table = 'usuario';
    private static $instance = null;
    
    public function __construct($debug = false)
    {
        parent::__construct(debug: $debug);
        if ($debug)
            echo "<p>Construtor de usuario</p>";
    }

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new Usuario();
        }
        return self::$instance;
    }

    // Criar usuário
    public function criar(array $data): array
    {
        $dados_obrigatorios = ['nome', 'email', 'senha'];
        $dados_permitidos = ['token','ativo'];
        // Valida dados passados
        if(!array_keys_exists($data, $dados_obrigatorios)){
            return criar_mensagem(
                false,
                'Há dados faltantes: '.implode(', ', array_diff(array_keys($dados_obrigatorios),array_keys($data))),
                [
                    'obrigatorios' => $dados_obrigatorios,
                    'permitidos' => $dados_permitidos,
                    'informados' => $data
                ]
            );
        }else{
            $data = array_keys_filter($data, array_merge($dados_obrigatorios, $dados_permitidos));
        }
        // Ativo
        if (isset($data['ativo'])) {
            $data['ativo'] = $data['ativo'] ? 1 : 0;
        } else {
            $data['ativo'] = 1;
        }
        // Nome
        if (empty($data['nome'])) {
            return criar_mensagem(false,'Nome vazio, informe um nome valido');
        }

        // E-mail válido
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return criar_mensagem(false,'E-mail invalido');
        }

        // E-mail disponível
        if (self::email_existe($data['email'])) {
            return criar_mensagem(false,'E-mail ja cadastrado');
        }

        // Senha válida
        if (empty($data['senha']) || strlen($data['senha']) < 6) {
            return criar_mensagem(false,'Senha deve ter pelo menos 6 caracteres');
        }

        // Criptografar senha
        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);

        // Inserir usuário no banco
        try {
            $id = $this->insert($data);
            return criar_mensagem(true,'Usuario criado com sucesso', ['id' => $id]);
        } catch (Exception $e) {
            return criar_mensagem(false,$e->getMessage());
        }
    }

    // Atualizar usuário
    public function atualizar(array $data): array
    {
        $dados_obrigatorios = ['id'];
        $dados_permitidos = ['nome','email','senha','ativo','token'];
        // Valida dados passados
        if(!array_keys_exists($data, $dados_obrigatorios)){
            return criar_mensagem(
                false,
                'Há dados faltantes',
                [
                    'obrigatorios' => $dados_obrigatorios,
                    'permitidos' => $dados_permitidos,
                    'informados' => $data]
            );
        }else{
            $data = array_keys_filter($data,array_merge($dados_obrigatorios, $dados_permitidos));
        }

        // Ativo
        if (isset($data['ativo'])) {
            $data['ativo'] = $data['ativo'] ? 1 : 0;
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
            $usuario_buscado = $this->fetch(['email' => $data['email']])[0] ?? null;
            if (!empty($usuario_buscado)) {
                if ($usuario_buscado['id'] != $data['id']) {
                    return [
                        'ok' => false,
                        'mensagem' => 'E-mail ja está sendo usado por outro usuario',
                        ['usuario_buscado' => $usuario_buscado]
                    ];
                } else {
                    // Se o e-mail pertence ao mesmo usuário, remover o e-mail dos dados para evitar atualização desnecessária
                    unset($data['email']);
                }
            }
        }

        // Senha
        if (isset($data['senha'])) {
            $usuario_atual = $this->fetch(['id' => $data['id']]) ?? null;
            if($usuario_atual){
                if (password_verify($data['senha'], $usuario_atual['senha'])) {
                    unset($data['senha']);
                } else {
                    $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
                }
            }else{
                throw new Exception("Não foi possível encontrar usuário com ID {$data['id']}", 1);
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
    public function deletar($data): array
    {
        $dados_obrigatorios = ['id'];
        $dados_permitidos = ['token'];
        // Valida dados passados
        if(!array_keys_exists($data, $dados_obrigatorios)){
            return criar_mensagem(
                false,
                'Há dados faltantes',
                ['obrigatorios' => $dados_obrigatorios,'permitidos' => $dados_permitidos]
            );
        }else{
            $data = array_keys_filter($data, array_merge($dados_obrigatorios, $dados_permitidos));
        }
        // Deletar no banco
        try {
            $data['id'] = explode(',',$data['id']);
            if($this->delete($data)){
                return criar_mensagem(true,"Registro deletado com sucesso");
            }else{
                return criar_mensagem(false,'Nenhum usuario com estes IDs foram encontrados');
            }
        } catch (Exception $e) {
            if($e->getCode() == 23503){
                return criar_mensagem(false, "Nao e possivel deletar $this->table pois outros registros dependem deste");
            }else{
                return criar_mensagem(false, $e->getMessage());
            }
        }
    }

    public function buscar($data){
        [$conditions, $fields, $limit, $offset] = parse_get_params($data);
        $params = [
            'conditions' => $conditions,
            'fields' => $fields,
            'limit' => $limit,
            'offset' => $offset
        ];
        try {
            $result = $this->search($conditions,$fields,$limit,$offset);
            if(empty($result)){
                return criar_mensagem(false,'Nenhum registro encontrado', ['query' => $params]);
            }{
                return criar_mensagem(true, 'Busca realizada com sucesso', ['lista' => $result]);
            }
        } catch (Exception $e) {
            return criar_mensagem(
                false, 
                'Houve um erro ao realizar busca',
                [
                    'detalhes' => $e->getMessage(),
                    'params' => json_encode($data)
                ]
            );
        }
    }

    // Verificar se um email já existe no banco
    public function email_existe($email): bool
    {
        return !empty($this->search(['email' => $email], ['id']));
    }
    // Função de login
    public function login(array $data): array
    {
        $dados_permitidos = ['token','email','senha'];
        //Tratando quando dados não são enviados
        if (empty($data)) {
            return criar_mensagem(false,'Informe login e senha ou token para realizar login',$data);
        }
        //Retirando dados desneccessários
        $data = array_keys_filter($data, $dados_permitidos);
        //Login
        if (isset($data['token'])) {
            // Login com token
            $usuario = $this->buscar_por_token($data['token'], ['id','email','senha','token','data_hora_expiracao_token']);
            if (!$usuario) {
                return criar_mensagem(false,'Token nao encontrado, realize login com email e senha');
            }else{
                if($usuario['data_hora_expiracao_token'] == null || $usuario['data_hora_expiracao_token'] == ''){
                    return criar_mensagem(false,'Token invalido, realize login com email e senha');
                }else{
                    if ($this->token_expirado($data['token'])) {
                        return criar_mensagem(false,'Token expirado, realize login com email e senha');
                    }
                }
            }
        }else {
            // Login com email e senha
            if(!isset($data['email']) || !isset($data['senha'])){
                return criar_mensagem(false,'Informe login e senha ou token para realizar login',$data);
            }else{
                if(empty($data['email']) || empty($data['senha'])){
                    return criar_mensagem(false,'Login ou senha vazios. Informe login e senha ou token para realizar login');
                }
            }
            //Buscando usuario
            $usuario = $this->search(['email' => $data['email']], ['id','email','senha','token','data_hora_expiracao_token'])[0] ?? null;
            //Checando senha
            if (!$usuario || !password_verify($data['senha'], $usuario['senha'])) {
                return criar_mensagem(false, 'Credenciais invalidas');
            }
        }

        // Atualiza ou gera um token
        if (is_null($usuario['token']) || $this->token_expirado($usuario['token'])) {
            // Gerar token
            $token = bin2hex(random_bytes(32));
        } else {
            // Manter token
            $token = $usuario['token'];
        }
        // Atualizar token
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 year'));
        $this->atualizar_token($usuario['id'], $token, $expiracao);
        return criar_mensagem(true, 'Login realizado com sucesso', ['token' => $token, 'data_hora_expiracao_token' => $expiracao]);
    }

    // Função de logout
    public function logout(string $token): array
    {
        if (empty($token)) {
            return criar_mensagem(false, 'Informe um token para realizar logout');
        }
        $usuario = $this->buscar_por_token($token,['id','token']);
        if (!$usuario) {
            return criar_mensagem(false, 'Token nao encontrado');
        }
        $linhas_afetadas = $this->inutilizar_token($usuario['id']);
        if($linhas_afetadas > 0){
            return criar_mensagem(true, 'Log out realizado com sucesso!');
        }else{
            return criar_mensagem(false, 'Log out nao pode ser realizado, token nao foi inutilizado');
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
        return $usuario;
    }

    // Função para inutilizar um token
    private function inutilizar_token($id)
    {
        $linhas_afetadas = $this->update(
            ['token' => null, 'data_hora_expiracao_token' => null, 'id' => $id]
        );
        return $linhas_afetadas;
    }

    // Função que atualiza o token de um usuário
    public function atualizar_token($id, $token, $expiracao): int
    {
        $linhas_afetadas = $this->update(
            ['token' => $token, 'data_hora_expiracao_token' => $expiracao, 'id' => $id]
        );
        return $linhas_afetadas;
    }

    // Função que verifica se token está expirado
    public function token_expirado($token): bool
    {
        $usuario = $this->buscar_por_token($token, ['data_hora_expiracao_token']);
        if (!$usuario) {
            return true;
        }
        if (strtotime($usuario['data_hora_expiracao_token']) < strtotime('now')) {
            return true;
        }
        return false;
    }

    // Função que valida se um token é válido
    public function token_valido($token): bool
    {
        $usuario = $this->buscar_por_token($token, ['id']);
        // Se existe
        if (!$usuario) {
            return false;
        }
        // Se está expirado
        if ($this->token_expirado($token)) {
            return false;
        }
        return true;
    }
}