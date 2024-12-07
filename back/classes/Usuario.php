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
        $dados_permitidos = [];
        //Tratar dados nulos
        if (empty($data)){
            return criar_mensagem(false,"Informe os campos obrigatorios: ".implode(',',$dados_obrigatorios));
        }
        // Verificar dados obrigatórios
        if(!array_keys_exists($data,$dados_obrigatorios)){
            return criar_mensagem(false,"Informe os campos obrigatorios: ".implode(',',$dados_obrigatorios));
        }
        //Retirar dados não necessários
        $data = array_intersect_key($data,array_flip(array_merge($dados_obrigatorios,$dados_permitidos)));

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
        $dados_opcionais = ['nome','email','senha','ativo'];
        $dados_permitidos = [];
        //Tratando quando nenhum dado é enviado
        if (empty($data)) {
            return criar_mensagem(false, 'Nenhum dado foi enviado. Dados obrigatorios: '.
                implode(', ',$dados_obrigatorios).". ".
                "Dados opcionais: ".
                implode(', ', $dados_opcionais)
            );
        }
        //Retirando dados desnecessários
        $data = array_intersect_key($data,array_flip(array_merge($dados_obrigatorios,$dados_opcionais,$dados_permitidos)));
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
    public function deletar($data): array
    {
        $dados_obrigatorios = ['id'];
        $dados_permitidos = [];
        //Tratando vazio
        if (empty($data)){
            return criar_mensagem(false,'ID e necessario para deletar usuarios');
        }
        //Retirando dados necessários
        $data = array_intersect_key($data,array_flip(array_merge($dados_obrigatorios,$dados_permitidos)));
        //Verificando IDs
        if(!isset($data['id'])){
            return criar_mensagem(false,'ID e necessario para deletar usuarios');
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
            return criar_mensagem(false,'Informe login e senha ou token para realizar login');
        }
        //Retirando dados desneccessários
        $data = array_intersect_key($data,array_flip($dados_permitidos));
        //Login
        if (isset($data['token'])) {
            // Login com token
            $usuario = $this->buscar_por_token($data['token'], ['id','email','senha','token','data_hora_expiracao_token']);
            if (!$usuario) {
                return criar_mensagem(false,'Token nao encontrado, realize login com email e senha');
            }else{
                if ($this->token_expirado($usuario['data_hora_expiracao_token'])) {
                    $this->inutilizar_token($data['token'], $usuario['id']);
                    return criar_mensagem(false,'Token expirado, realize login com email e senha');
                }
            }
        }else {
            // Login com email e senha
            if(!isset($data['email']) || !isset($data['senha'])){
                return criar_mensagem(false,'Informe login e senha ou token para realizar login');
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
                //Por questões de segurança, é ideal dizer que credenciais estão invalidas do que dizer se só a senha está errada, se email não foi encontrado, etc.
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
        $linhas_afetadas = $this->inutilizar_token($token, $usuario['id']);
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
        $linhas_afetadas = $this->update(
            ['token' => null, 'data_hora_expiracao_token' => null, 'id' => $id]
        );
        return $linhas_afetadas;
    }
}