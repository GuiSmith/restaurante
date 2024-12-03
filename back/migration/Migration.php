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
                    id_registro INT,
                    operacao crud,
                    id_usuario INT,
                    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    registro TEXT
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
                    data_hora_atualizacao TIMESTAMP
                );
            ",
            'item' => "
                CREATE TABLE IF NOT EXISTS item (
                    id SERIAL PRIMARY KEY,
                    descricao VARCHAR(255) NOT NULL,
                    valor DECIMAL(10, 2) NOT NULL,
                    tipo VARCHAR(20),
                    ativo BOOLEAN NOT NULL DEFAULT TRUE,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_hora_atualizacao TIMESTAMP
                );
            ",
            'comanda' => "
                CREATE TABLE IF NOT EXISTS comanda (
                    id SERIAL PRIMARY KEY,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_hora_atualizacao TIMESTAMP,
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
            'pedido' => "
                CREATE TABLE IF NOT EXISTS pedido (
                    id SERIAL PRIMARY KEY,
                    id_comanda INT,
                    quantidade INT NOT NULL CHECK (quantidade > 0),
                    data_hora_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    data_hora_atualizacao TIMESTAMP,
                    data_hora_confirmacao TIMESTAMP,
                    data_hora_pronto TIMESTAMP,
                    data_hora_entregue TIMESTAMP,
                    status VARCHAR(20) NOT NULL,
                    destino VARCHAR(20) NOT NULL,
                    FOREIGN KEY (id_comanda) REFERENCES comanda(id)
                );
            ",
            'pedido_item' => "
                CREATE TABLE IF NOT EXISTS pedido_item (
                    id_pedido INT,
                    id_item INT,
                    quantidade INT NOT NULL CHECK (quantidade > 0),
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_hora_atualizacao TIMESTAMP,
                    PRIMARY KEY (id_pedido, id_item),
                    FOREIGN KEY (id_pedido) REFERENCES pedido(id),
                    FOREIGN KEY (id_item) REFERENCES item(id)
                );
            ",
            'pagamento' => "
                CREATE TABLE IF NOT EXISTS pagamento (
                    id SERIAL PRIMARY KEY,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    forma_pagamento VARCHAR(50) NOT NULL,
                    valor DECIMAL(10, 2) NOT NULL,
                    id_comanda INT,
                    FOREIGN KEY (id_comanda) REFERENCES comanda(id)
                );
            "
        ],
        'functions' => [
            'atualizar_data_hora_atualizacao' => "
                CREATE OR REPLACE FUNCTION update_data_hora_atualizacao()
                RETURNS TRIGGER AS $$
                BEGIN
                    -- Verificar se a coluna 'data_hora_atualizacao' existe na tabela
                    IF EXISTS (
                        SELECT 1
                        FROM information_schema.columns
                        WHERE table_name = TG_TABLE_NAME
                        AND column_name = 'data_hora_atualizacao'
                    ) THEN
                        -- Atualizar a coluna 'data_hora_atualizacao' se ela existir
                        NEW.data_hora_atualizacao = CURRENT_TIMESTAMP;
                    END IF;

                    -- Retornar o registro alterado
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ",
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

            // Criando triggers
            echo "<h2>Criando TRIGGERS de atualizar data_hora_atualizacao...</h2>";
            $tables_com_data_hora_atualizacao = [
                'usuario', 
                'item',
                'pedido_item',
                'pedido',
                'comanda'
            ];
            foreach ($tables_com_data_hora_atualizacao as $table) {
                try {
                    echo "<p>Criando trigger da tabela: '$table'...</p>";
                    $sql = "SELECT criar_trigger('$table', 'update_data_hora_atualizacao')";
                    $this->db->query("SAVEPOINT savepoint_trigger_$table;");
                    $this->db->query($sql);
                    echo "<p>Trigger da tabela '$table' criado com sucesso!</p>";
                } catch (PDOException $e) {
                    // Ignorar erros de duplicação de triggers
                    if (in_array($e->getCode(), ['42710'])) {
                        echo "<p>Ignorado: Trigger da tabela '$table' já existe.</p>";
                        $this->db->query("ROLLBACK TO savepoint_trigger_$table;");
                    } else {
                        throw $e;
                    }
                }
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