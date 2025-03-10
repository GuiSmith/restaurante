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

    //Trata objeto de erro de BD para mensagem PT-BR
    function db_catch_to_string($e) {
        $mensagem = $e->getMessage();
    
        return match (true) {
            strpos($mensagem, "column") !== false && strpos($mensagem, "does not exist") !== false =>
                "Erro: A coluna especificada não existe nesta tabela do banco de dados. Verifique o nome da coluna.",
            
            strpos($mensagem, "invalid input value for enum") !== false =>
                "Erro: O valor inserido não se encaixa nos valores pre definidos. Verifique os valores permitidos.",
            
            strpos($mensagem, "violates unique constraint") !== false =>
                "Erro: Tentativa de inserir um valor duplicado em uma coluna que exige valores únicos. Verifique os dados enviados.",
            
            strpos($mensagem, "syntax error at or near") !== false =>
                "Erro: Há um erro de sintaxe na sua consulta SQL. Verifique a query enviada ao banco de dados.",
            
            default => "Erro desconhecido no banco de dados: " . $mensagem,
        };
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function id_user($token) {
        if (!is_string($token)) return null;
        $usuario = $this->fetch('usuario',['token' => $token]);
        return $usuario ? $usuario['id'] : null;
    }

    public function filter_columns(string $table, array $columns): array
    {
        if(empty($columns)) return [];
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $sql = 
            "SELECT column_name
            FROM information_schema.columns
            WHERE table_name = ?
                AND column_name IN ($placeholders)
        ";
        $params = array_merge([$table],$columns);
        $result = $this->fetchAll($sql,$params);
        $column_names = array_map(function($row){
            return $row['column_name'];
        },$result);
        return $column_names;
    }

    public function fetch(string $table, array $unique_key){
        $key = array_keys($unique_key)[0];
        $sql = "SELECT * FROM $table WHERE $key = :$key";
        return $this->query($sql,$unique_key)->fetch();
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function search($table, $conditions = [], $fields = [], $limit = null, $offset = null, $order_by = null)
    {
        // Mudando valores de status para maiúsculos
        if(array_key_exists('status',$conditions)){
            $conditions['status'] = strtoupper($conditions['status']);
        }

        // Filtrando campos de condições
        $condition_keys = array_keys($conditions);
        $filtered_keys = $this->filter_columns($table,$condition_keys);
        $conditions = array_intersect_key($conditions, array_flip($filtered_keys));

        // Filtrando colunas
        $fields = $this->filter_columns($table, $fields);

        // Se $fields for um array, converte para uma string separada por vírgulas
        $fields = !empty($fields) ? implode(", ", $fields) : '*';

        // Cria a parte do WHERE com base nas condições fornecidas
        $where = "";
        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        }

        // Prepara a consulta SQL
        $sql = "SELECT $fields FROM $table $where";

        // LIMIT
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $conditions['limit'] = $limit;
        }

        // OFFSET
        if ($offset !== null) {
            $sql .= " OFFSET :offset";
            $conditions['offset'] = $offset;
        }

        // ORDER BY
        if($order_by !== null && is_array($order_by)){
            $filtered_order_by = $this->filter_columns($table, current($order_by));
            if(count($filtered_order_by) > 0){
                $sql .= " ORDER BY ".current($filtered_order_by)." ASC";
            }
        }

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