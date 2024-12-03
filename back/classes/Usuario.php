<?php

require_once 'CRUDModel.php';

class Usuario extends CRUDModel
{
    protected static $table = 'usuario';

    public function __construct($debug = false)
    {
        parent::__construct(debug: $debug);
        if ($debug) echo "<p>Construtor de usuario</p>";
    }

    // Criar usuário
    public function criar(array $data): array
    {
        // Verificar dados obrigatórios
        $dados_obrigatorios = ['nome', 'email', 'senha'];
        foreach ($dados_obrigatorios as $dado_obrigatorio) {
            if (!array_key_exists($dado_obrigatorio, $data)) {
                return ['ok' => false, 'mensagem' => "'$dado_obrigatorio' é necessário"];
            }
        }

        // Nome
        if (empty($data['nome'])) {
            return ['ok' => false, 'mensagem' => 'Nome é obrigatório'];
        }

        // E-mail válido
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mensagem' => 'E-mail inválido'];
        }

        // E-mail disponível
        if (self::email_existe($data['email'])) {
            return ['ok' => false, 'mensagem' => 'E-mail já cadastrado'];
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
            return ['ok' => true, 'mensagem' => 'Usuário criado com sucesso', 'id' => $id];
        } catch (Exception $e) {
            return ['ok' => false, 'mensagem' => $e->getMessage()];
        }
    }

    // Atualizar usuário
    public function atualizar(array $data): array
    {
        // ID obrigatório
        if (!isset($data['id'])) {
            return ['ok' => false, 'mensagem' => 'ID é obrigatório'];
        }

        // Nome válido
        if (isset($data['nome']) && empty($data['nome'])) {
            return ['ok' => false, 'mensagem' => 'Nome não pode estar vazio'];
        }

        // E-mail válido
        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'mensagem' => 'E-mail inválido'];
            }

            // Verificar se o e-mail pertence a outro usuário
            $id_do_email_buscado = $this->search(['email' => $data['email']], ['id']);
            if (!empty($id_do_email_buscado) && $id_do_email_buscado[0]['id'] != $data['id']) {
                return ['ok' => false, 'mensagem' => 'E-mail já está sendo usado por outro usuário'];
            }
        }

        // Atualizar no banco
        try {
            $this->update($data, "id = :id");
            return ['ok' => true, 'mensagem' => 'Usuário atualizado com sucesso'];
        } catch (Exception $e) {
            return ['ok' => false, 'mensagem' => $e->getMessage()];
        }
    }

    // Deletar usuário
    public function deletar($ids): array
    {
        // ID obrigatório
        if (empty($ids)) {
            return ['ok' => false, 'mensagem' => 'ID é obrigatório para deletar'];
        }

        $ids_string = implode(separator: ',',array: $ids);

        // Deletar no banco
        try { 
            $result = $this->delete("id IN '($ids_string)'");
            $linhas_afetadas = $result->rowCount();
            return ['ok' => true, 'mensagem' => 'Usuário deletado com sucesso', 'linhas_afetadas' => $linhas_afetadas];
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
    public function login(string $email, string $senha): array
    {
        // Verificar se o e-mail foi fornecido
        if (empty($email)) {
            return ['ok' => false, 'erro' => 'E-mail é obrigatório'];
        }

        // Verificar se a senha foi fornecida
        if (empty($senha)) {
            return ['ok' => false, 'erro' => 'Senha é obrigatória'];
        }

        // Buscar usuário pelo e-mail
        $usuario = $this->search(['email' => $email], ['id', 'senha', 'nome']);
        if (empty($usuario)) {
            return ['ok' => false, 'erro' => 'Usuário não encontrado'];
        }

        // Verificar a senha
        if (!password_verify($senha, $usuario[0]['senha'])) {
            return ['ok' => false, 'erro' => 'Senha incorreta'];
        }

        // Armazenar informações na sessão
        session_start();
        $_SESSION['id_usuario'] = $usuario[0]['id'];

        return ['ok' => true, 'mensagem' => 'Login realizado com sucesso', 'usuario' => $usuario[0]];
    }

    // Função de logout
    public function log_out(): array
    {
        // Inicia ou retoma a sessão
        session_start();

        // Destruir a sessão
        session_unset();
        session_destroy();

        return ['ok' => true, 'mensagem' => 'Logout realizado com sucesso'];
    }

}