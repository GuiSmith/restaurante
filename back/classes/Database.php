<?php

class Database
{
    private static $instance = null;
    private $pdo;
    private $host = "localhost";
    private $db = "restaurante";
    private $user = "smith";
    private $pass = "Dansiguer@2014";

    private function __construct()
    {
        // Conexão com PostgreSQL
        $dsn = "pgsql:host=$this->host;port=5432;dbname=$this->db;user=$this->user;password=$this->pass";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Iniciar uma transação
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    // Fazer commit na transação
    public function commit()
    {
        $this->pdo->commit();
    }

    // Fazer rollback na transação
    public function rollback()
    {
        $this->pdo->rollBack();
    }

    // Verificar se uma transação está ativa
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function id_user($token) {
        if (!is_string($token)) return null;
        $usuario = $this->search('usuario',['token' => $token],['id']);
        return !empty($usuario) ? $usuario[0]['id'] : null;
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function select_all($table){
        return $this->query("SELECT * FROM $table")->fetchAll();
    }

    public function search($table, $conditions = [], $fields = [])
    {
        // Se $fields for um array, converte para uma string separada por vírgulas
        $fields = !empty($fields) ? implode(", ", $fields) : '*';

        // Cria a parte do WHERE com base nas condições fornecidas
        $where = "";
        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        }

        // Prepara a consulta SQL
        $sql = "SELECT $fields FROM $table $where";
        // Executa a consulta e retorna os resultados
        return $this->fetchAll($sql, $conditions);
    }

    public function insert($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);
        if(is_bool($stmt) && $stmt == false){
            return 0;
        }else{
            return $this->pdo->lastInsertId();
        }
    }   

    public function update($table, $data)
    {
        $set = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
        $sql = "UPDATE $table SET $set WHERE id = :id";
        $update_query = $this->pdo->prepare($sql);
        $update_query->execute($data);
        return $update_query->rowCount();
    }

    public function delete($table,$ids = []): bool|PDOStatement
    {
        $placeholders = implode(',',array_fill(0,count($ids),'?'));
        $sql = "DELETE FROM $table WHERE id IN($placeholders)";
        $query = $this->pdo->prepare($sql);
        $query->execute($ids);
        return $query->rowCount();
    }
}

?>