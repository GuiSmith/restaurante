<?php

require_once '../classes/Database.php';

class Migration
{
    private $db;

    // Construtor recebe uma instância de Database (PDO)
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private $schemas = [
        'types' => [
            'CRUD' => "
                CREATE TYPE crud AS ENUM ('CREATE', 'SELECT', 'UPDATE', 'DELETE');
            "
        ],
        'tables' => [
            'log' => "
                CREATE TABLE IF NOT EXISTS log(
                    id SERIAL PRIMARY KEY,
                    tabela VARCHAR(50),
                    operacao crud,
                    id_usuario INT,
                    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    registro JSON
                )
            ",
            'usuario' => "
                CREATE TABLE IF NOT EXISTS usuario (
                    id SERIAL PRIMARY KEY,
                    nome VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    senha VARCHAR(255) NOT NULL,
                    ativo BOOLEAN NOT NULL DEFAULT TRUE,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_hora_atualizacao TIMESTAMP,
                    token VARCHAR(255),
                    data_expiracao_token TIMESTAMP
                );
            ",
            'item' => "
                CREATE TABLE IF NOT EXISTS item (
                    id SERIAL PRIMARY KEY,
                    descricao VARCHAR(255) NOT NULL,
                    valor DECIMAL(10, 2) NOT NULL,
                    tipo VARCHAR(20) NOT NULL,
                    ativo BOOLEAN DEFAULT TRUE,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ",
            'comanda' => "
                CREATE TABLE IF NOT EXISTS comanda (
                    id SERIAL PRIMARY KEY,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_hora_fechamento TIMESTAMP,
                    status VARCHAR(20) NOT NULL,
                    valor_total DECIMAL(10, 2) NOT NULL,
                    valor_baixado DECIMAL(10, 2) NOT NULL,
                    valor_recebido DECIMAL(10, 2) NOT NULL,
                    valor_aberto DECIMAL(10, 2) NOT NULL,
                    acrescimos DECIMAL(10, 2),
                    descontos DECIMAL(10, 2)
                );
            ",
            'pedido_item' => "
                CREATE TABLE IF NOT EXISTS pedido_item (
                    id SERIAL PRIMARY KEY,
                    id_item INT,
                    id_comanda INT,
                    quantidade INT NOT NULL CHECK (quantidade > 0),
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_comanda) REFERENCES comanda(id),
                    FOREIGN KEY (id_item) REFERENCES item(id)
                );
            ",
            'pagamento' => "
                CREATE TABLE IF NOT EXISTS pagamento (
                    id SERIAL PRIMARY KEY,
                    id_comanda INT,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    forma_pagamento VARCHAR(50) NOT NULL,
                    valor DECIMAL(10, 2) NOT NULL,
                    FOREIGN KEY (id_comanda) REFERENCES comanda(id)
                );
            "
        ],
        'functions' => [
            'criar_trigger' => "
                CREATE OR REPLACE FUNCTION criar_trigger(
                    nome_tabela TEXT,
                    nome_funcao TEXT
                )
                RETURNS VOID AS $$
                BEGIN
                    EXECUTE format(
                        'CREATE TRIGGER trigger_%I_%I
                        BEFORE UPDATE ON %I
                        FOR EACH ROW
                        EXECUTE FUNCTION %I();',
                        nome_funcao, nome_tabela, nome_tabela, nome_funcao
                    );
                END;
                $$ LANGUAGE plpgsql;
            "
        ]
    ];

    // Função que executa as migrações
    public function migrate()
    {
        try {
            echo "<h1>Migrando banco de dados</h1>";
            $this->db->beginTransaction(); // Inicia a transação principal

            foreach ($this->schemas as $schema_type => $schema_data) {
                echo "<h2>Criando '$schema_type'...</h2>";
                foreach ($schema_data as $schema_name => $sql) {
                    try {
                        echo "<p>Criando '$schema_name'...</p>";

                        // Criação de SAVEPOINT para isolar erros
                        $this->db->query("SAVEPOINT savepoint_$schema_name;");
                        $this->db->query($sql);
                        echo "<p>'$schema_name' criado com sucesso!</p>";
                    } catch (PDOException $e) {
                        // Ignorar erros de duplicação e reverter ao SAVEPOINT
                        if (in_array($e->getCode(), ['42710', '42P07'])) {
                            echo "<p>Ignorado: '$schema_name' já existe.</p>";
                            $this->db->query("ROLLBACK TO savepoint_$schema_name;");
                        } else {
                            // Relançar exceções críticas
                            throw $e;
                        }
                    }
                }
                echo "<h2>'$schema_type' criados com sucesso!</h2>";
            }

            $this->db->commit(); // Confirma a transação após todas as tabelas e triggers serem criadas
            echo "<h1>Banco de dados migrado com sucesso!</h1>";
            return true;
        } catch (Exception $e) {
            // Reverte toda a transação em caso de erro crítico
            $this->db->rollback();
            echo "Erro ao migrar o banco de dados: " . $e->getMessage() . "<br>";
            return false;
        }
    }


}

?>