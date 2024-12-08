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
        'type' => [
            'crud' => "
                CREATE TYPE crud AS ENUM ('INSERT', 'SELECT', 'UPDATE', 'DELETE');
            ",
            'comanda_status' => "
                CREATE TYPE comanda_status AS ENUM ('aberta', 'fechada');
            "
        ],
        'table' => [
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
                    data_hora_expiracao_token TIMESTAMP
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
                    data_hora_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_hora_fechamento TIMESTAMP,
                    status comanda_status NOT NULL,
                    valor_total DECIMAL(10, 2) DEFAULT 0.00,
                    valor_recebido DECIMAL(10, 2) DEFAULT 0.00,
                    descontos DECIMAL(10, 2) DEFAULT 0.00
                );
            ",
            'item_comanda' => "
                CREATE TABLE IF NOT EXISTS item_comanda (
                    id SERIAL PRIMARY KEY,
                    id_item INT NOT NULL,
                    id_comanda INT NOT NULL,
                    quantidade INT NOT NULL CHECK (quantidade > 0),
                    status VARCHAR(100),
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    descontos DECIMAL(10,2) DEFAULT 0.00,
                    isento BOOLEAN DEFAULT FALSE,
                    FOREIGN KEY (id_comanda) REFERENCES comanda(id),
                    FOREIGN KEY (id_item) REFERENCES item(id)
                );
            ",
            'pagamento' => "
                CREATE TABLE IF NOT EXISTS pagamento (
                    id SERIAL PRIMARY KEY,
                    id_comanda INT NOT NULL,
                    data_hora_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    forma_pagamento VARCHAR(50) NOT NULL,
                    valor DECIMAL(10, 2) NOT NULL,
                    FOREIGN KEY (id_comanda) REFERENCES comanda(id)
                );
            ",
            "log_item_comanda" => "
                CREATE TABLE IF NOT EXISTS log_item_comanda(
                    id SERIAL PRIMARY KEY,
                    id_item_comanda INT NOT NULL,
                    status VARCHAR(100),
                    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            "
        ],
        'function' => [
            'atualizar_comanda_em_item_comanda' =>
            "
                CREATE OR REPLACE FUNCTION atualizar_comanda_em_item_comanda()
                RETURNS TRIGGER AS $$
                DECLARE
                    total_comanda DECIMAL(10, 2);
                    total_descontos DECIMAL(10, 2);
                BEGIN
                    -- Determinar o id_comanda relevante
                    IF (TG_OP = 'DELETE') THEN
                        SELECT COALESCE(SUM(pi.quantidade * i.valor), 0)
                        INTO total_comanda
                        FROM item_comanda pi
                        JOIN item i ON pi.id_item = i.id
                        WHERE pi.id_comanda = OLD.id_comanda;

                        SELECT COALESCE(SUM(CASE 
                            WHEN pi.isento THEN pi.quantidade * i.valor
                            ELSE pi.descontos
                        END), 0)
                        INTO total_descontos
                        FROM item_comanda pi
                        JOIN item i ON pi.id_item = i.id
                        WHERE pi.id_comanda = OLD.id_comanda;

                        -- Atualizar a comanda
                        UPDATE comanda
                        SET 
                            valor_total = total_comanda,
                            descontos = total_descontos

                        WHERE id = OLD.id_comanda;

                    ELSE
                        SELECT COALESCE(SUM(pi.quantidade * i.valor), 0)
                        INTO total_comanda
                        FROM item_comanda pi
                        JOIN item i ON pi.id_item = i.id
                        WHERE pi.id_comanda = NEW.id_comanda;

                        SELECT COALESCE(SUM(CASE 
                            WHEN pi.isento THEN pi.quantidade * i.valor
                            ELSE pi.descontos
                        END), 0)
                        INTO total_descontos
                        FROM item_comanda pi
                        JOIN item i ON pi.id_item = i.id
                        WHERE pi.id_comanda = NEW.id_comanda;

                        -- Atualizar a comanda
                        UPDATE comanda
                        SET 
                            valor_total = total_comanda,
                            descontos = total_descontos
                        WHERE id = NEW.id_comanda;
                    END IF;

                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ",
            'criar_log_item_comanda' => "
                CREATE OR REPLACE FUNCTION criar_log_item_comanda()
                RETURNS TRIGGER AS $$
                BEGIN
                    INSERT INTO log_item_comanda (id_item_comanda, status) VALUES (NEW.id, NEW.status);
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ",
            "atualizar_comanda_em_pagamento" => "
                CREATE OR REPLACE FUNCTION atualizar_comanda_em_pagamento()
                RETURNS TRIGGER AS $$
                DECLARE
                    var_comanda RECORD;
                BEGIN
                    IF (TG_OP = 'DELETE') THEN
                        UPDATE comanda
                        SET
                            valor_recebido = valor_recebido - OLD.valor
                        WHERE id = OLD.id_comanda;
                    ELSE
                        SELECT c.valor_recebido, c.valor_total
                        INTO var_comanda
                        FROM comanda AS c
                        WHERE c.id = NEW.id_comanda;

                        IF var_comanda.valor_recebido + NEW.valor > var_comanda.valor_total THEN
                            RAISE EXCEPTION 'Valor recebido ira ultrapassar valor total da comanda' USING ERRCODE = 'P0001';
                        ELSE
                            UPDATE comanda
                            SET valor_recebido = valor_recebido + NEW.valor
                            WHERE comanda.id = NEW.id_comanda;
                        END IF;
                    END IF;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ",
            'atualizar_data_fechamento_comanda' => "
                CREATE OR REPLACE FUNCTION atualizar_data_fechamento_comanda()
                RETURNS TRIGGER AS $$
                BEGIN
                    IF NEW.status = 'fechada' THEN
                        NEW.data_hora_fechamento = NOW();
                    END IF;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ",
            'verificar_itens_em_aberto' => "
                CREATE OR REPLACE FUNCTION verificar_itens_em_aberto(p_id_comanda INT)
                RETURNS BOOLEAN AS $$
                DECLARE
                    itens_abertos INT;
                BEGIN
                    -- Conta os itens da comanda que estão com status diferente de 'fechado' ou com quantidade > 0
                    SELECT COUNT(id)
                    INTO itens_abertos
                    FROM item_comanda
                    WHERE item_comanda.id_comanda = p_id_comanda
                    AND (status IS NULL OR status <> 'entregue')
                    AND quantidade > 0;

                    -- Se houver itens abertos, retorna TRUE, caso contrário, FALSE
                    IF itens_abertos > 0 THEN
                        RETURN TRUE;
                    ELSE
                        RETURN FALSE;
                    END IF;
                END;
                $$ LANGUAGE plpgsql;
            "
        ],
        'trigger' => [
            "atualizar_comanda_em_item_comanda" => "
                CREATE TRIGGER atualizar_comanda_em_item_comanda
                AFTER INSERT OR UPDATE OR DELETE
                ON item_comanda
                FOR EACH ROW
                EXECUTE FUNCTION atualizar_comanda_em_item_comanda();
            ",
            "trigger_criar_log_item_comanda" => "
                CREATE OR REPLACE TRIGGER item_comanda_log_trigger
                BEFORE INSERT OR UPDATE OR DELETE ON item_comanda
                FOR EACH ROW
                EXECUTE FUNCTION criar_log_item_comanda();
            ",
            "trigger_atualizar_comanda_em_pagamento" => "
                CREATE OR REPLACE TRIGGER trigger_atualizar_comanda_em_pagamento
                BEFORE INSERT OR DELETE ON pagamento
                FOR EACH ROW
                EXECUTE FUNCTION atualizar_comanda_em_pagamento();
            ",
            'trigger_atualizar_data_fechamento_comanda' => "
                CREATE OR REPLACE TRIGGER trigger_atualizar_data_fechamento_comanda
                BEFORE UPDATE ON comanda
                FOR EACH ROW
                EXECUTE FUNCTION atualizar_data_fechamento_comanda();
            "
        ],
        'view' => [
            'ordens_producao' => "
                CREATE VIEW ordens_producao AS
                SELECT
                    ic.id,
                    ic.id_comanda,
                    CASE
                        WHEN i.tipo = 'bebida' THEN 'copa'
                        WHEN i.tipo = 'prato' THEN 'cozinha'
                        ELSE i.tipo
                        END AS destino,
                    i.descricao,
                    ic.quantidade,
                    i.valor AS valor_unitario,
                    i.valor * ic.quantidade AS sub_total,
                    ic.data_hora_cadastro,
                    ic.descontos,
                    ic.isento
                FROM item_comanda AS ic
                JOIN item AS i
                ON ic.id_item = i.id
                WHERE ic.status <> 'entregue';
            ",
            'ordens_producao_cozinha' => "
                CREATE OR REPLACE VIEW ordens_producao_cozinha AS
                SELECT 
                    *
                FROM 
                    ordens_producao
                WHERE 
                    destino = 'cozinha';
            ",
            'ordens_producao_copa' => "
                CREATE OR REPLACE VIEW ordens_producao_copa AS
                SELECT 
                    *
                FROM 
                    ordens_producao
                WHERE 
                    destino = 'copa';
            ",
            'vendas_do_dia' => "
                CREATE OR REPLACE VIEW vendas_do_dia AS
                SELECT 
                    c.id AS id_comanda,
                    c.data_hora_abertura,
                    COALESCE(c.data_hora_fechamento, CURRENT_TIMESTAMP) AS data_hora_fechamento,
                    c.valor_total,
                    c.valor_recebido,
                    c.descontos,
                    SUM(ic.quantidade * i.valor) AS total_itens,
                    COUNT(DISTINCT p.id) AS quantidade_pagamentos,
                    ARRAY_AGG(DISTINCT p.forma_pagamento) AS formas_pagamento,
                    ARRAY_AGG(DISTINCT i.descricao) AS itens_comanda,
                    COUNT(DISTINCT ic.id_item) AS itens_diferentes
                FROM 
                    comanda c
                LEFT JOIN 
                    pagamento p ON c.id = p.id_comanda
                LEFT JOIN 
                    item_comanda ic ON c.id = ic.id_comanda
                LEFT JOIN 
                    item i ON ic.id_item = i.id
                WHERE 
                    DATE(c.data_hora_abertura) = CURRENT_DATE
                GROUP BY 
                    c.id, c.data_hora_abertura, c.data_hora_fechamento, c.valor_total, c.valor_recebido, c.descontos
                ORDER BY 
                    c.data_hora_abertura;
            "
        ]
    ];

    private $trigger_tables = [
        'atualizar_comanda_em_item_comanda' => 'item_comanda',
        'trigger_criar_log_item_comanda' => 'log_item_comanda',
        'trigger_atualizar_comanda_em_pagamento' => 'pagamento',
        'trigger_atualizar_data_fechamento_comanda' => 'comanda'
    ];

    public function schemas(){
        return $this->schemas;
    }

    public function drop($schema_type, $schema_name, $cascade = false){
        $schema_type = strtoupper($schema_type);
        $sql = "DROP $schema_type IF EXISTS $schema_name ";
        if($schema_type == 'TRIGGER'){
            if(isset($trigger_tables[$schema_name])){
                $sql .= "ON ".$this->trigger_tables[$schema_name];
            }else{
                return false;
            }
        }else{
            if($cascade) $sql .= "CASCADE";
        }
        echo $sql."<br>";
        return $this->db->query($sql);
    }

    public function drop_schemas(){
        try {
            echo "<h1>Derrubando Banco de dados</h1>";
            $this->db->beginTransaction();
            foreach ($this->schemas as $schema_type => $schema_name_list) {
                echo "<h2>Derrubando $schema_type</h2>";   
                foreach (array_keys($schema_name_list) as $schema_name) {
                    echo "Derrubando $schema_name<br>";
                    $this->drop($schema_type,$schema_name, true);
                    echo "<b style='color:green'>
                    $schema_name derrubado
                    </b>
                    <br><br>";
                }
            }
            $this->db->commit();
            echo "<h1>Banco de dados derrubado com sucesso!</h1>";
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            echo "<h3>Erro ao derrubar banco de dados</h3>";
            echo "<p>".$e->getMessage()."</p>";
            return false;
        }
    }

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