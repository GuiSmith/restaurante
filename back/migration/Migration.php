<?php

require_once '../Database.php';

class Migration
{
    private $db;

    // Construtor recebe uma instância de Database (PDO)
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Array associativo com SQL de criação de tabelas
    private $tables = [
        'Usuario' => "
            CREATE TABLE IF NOT EXISTS Usuario (
                ID SERIAL PRIMARY KEY,
                Nome VARCHAR(100) NOT NULL,
                Email VARCHAR(100) UNIQUE NOT NULL,
                Login VARCHAR(50) UNIQUE NOT NULL,
                Senha VARCHAR(255) NOT NULL,
                Ativo BOOLEAN NOT NULL DEFAULT TRUE
            );
        ",
        'Item' => "
            CREATE TABLE IF NOT EXISTS Item (
                ID SERIAL PRIMARY KEY,
                Descricao VARCHAR(255) NOT NULL,
                Valor DECIMAL(10, 2) NOT NULL,
                Tipo VARCHAR(20),
                Ativo BOOLEAN NOT NULL DEFAULT TRUE
            );
        ",
        'Comanda' => "
            CREATE TABLE IF NOT EXISTS Comanda (
                ID SERIAL PRIMARY KEY,
                Valor DECIMAL(10, 2) NOT NULL,
                DataHoraAbertura TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                DataHoraFechamento TIMESTAMP,
                Descontos BOOLEAN NOT NULL,
                Status VARCHAR(20) NOT NULL,
                UsuarioID INT,
                FOREIGN KEY (UsuarioID) REFERENCES Usuario(ID)
            );
        ",
        'Pedido' => "
            CREATE TABLE IF NOT EXISTS Pedido (
                ID SERIAL PRIMARY KEY,
                Quantidade INT NOT NULL CHECK (Quantidade > 0),
                DataHoraCadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                DataHoraConfirmacao TIMESTAMP,
                DataHoraPronto TIMESTAMP,
                DataHoraEntregue TIMESTAMP,
                Status VARCHAR(20) NOT NULL,
                Destino VARCHAR(20) NOT NULL,
                ComandaID INT,
                FOREIGN KEY (ComandaID) REFERENCES Comanda(ID) ON DELETE CASCADE
            );
        ",
        'Pedido_Item' => "
            CREATE TABLE IF NOT EXISTS Pedido_Item (
                PedidoID INT,
                ItemID INT,
                Quantidade INT NOT NULL CHECK (Quantidade > 0),
                PRIMARY KEY (PedidoID, ItemID),
                FOREIGN KEY (PedidoID) REFERENCES Pedido(ID) ON DELETE CASCADE,
                FOREIGN KEY (ItemID) REFERENCES Item(ID)
            );
        ",
        'Pagamento' => "
            CREATE TABLE IF NOT EXISTS Pagamento (
                ID SERIAL PRIMARY KEY,
                DataHora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FormaPagamento VARCHAR(20) NOT NULL,
                Valor DECIMAL(10, 2) NOT NULL,
                UsuarioRecebimentoID INT,
                ComandaID INT,
                FOREIGN KEY (UsuarioRecebimentoID) REFERENCES Usuario(ID),
                FOREIGN KEY (ComandaID) REFERENCES Comanda(ID) ON DELETE CASCADE
            );
        "
    ];

    // Função que executa as migrações
    public function migrate()
    {
        foreach ($this->tables as $table => $sql) {
            $this->executeQuery($sql);
            echo "Tabela '$table' criada com sucesso.<br>";
        }
    }

    // Função para executar o SQL
    private function executeQuery($sql)
    {
        try {
            $stmt = $this->db->query($sql); // Usando query diretamente
            $stmt->execute(); // Executa a query
        } catch (PDOException $e) {
            die("Erro ao executar a migração: " . $e->getMessage());
        }
    }
}

?>