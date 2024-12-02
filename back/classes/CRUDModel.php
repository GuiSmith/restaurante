<?php

require_once "Database.php";

class CRUDModel
{
    protected static $db;
    protected static $table; // Nome da tabela associada

    public function __construct($debug = false)
    {
        if (!self::$db) {
            self::$db = Database::getInstance();
            if ($debug) {
                echo "<p>construtor de CRUDModel</p>";
                var_dump(self::$db);
            }
        }
    }

    public function insert(array $data): bool|string
    {
        return self::$db->insert(static::$table, $data);
    }

    public function update(array $data, $condition): void
    {
        self::$db->update(static::$table, $data, $condition);
    }

    public function delete($condition, $params = []): bool|PDOStatement
    {
        return self::$db->delete(static::$table, $condition, $params);
    }

    public function find($id): mixed
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";
        return self::$db->fetch($sql, ['id' => $id]);
    }

    public function all()
    {
        $sql = "SELECT * FROM " . static::$table;
        //var_dump(self::$db);
        return self::$db->fetchAll($sql);
    }

    public function search(array $conditions = [], array $fields = [])
    {
        if (empty($conditions) && empty($fields)){
            return $this->all();
        }else{
            return self::$db->search(static::$table, $conditions, $fields);
        }
    }
}


?>