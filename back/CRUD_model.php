<?php

require_once "Database.php";

class CRUDModel
{
    protected static $db;
    protected static $table; // Nome da tabela associada

    public function __construct()
    {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
    }

    public static function insert(array $data): bool|string
    {
        return self::$db->insert(static::$table, $data);
    }

    public static function update(array $data, $condition): void
    {
        self::$db->update(static::$table, $data, $condition);
    }

    public static function delete($condition, $params = []): void
    {
        self::$db->delete(static::$table, $condition, $params);
    }

    public static function find($id): mixed
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";
        return self::$db->fetch($sql, ['id' => $id]);
    }

    public static function all(): array
    {
        $sql = "SELECT * FROM " . static::$table;
        return self::$db->fetchAll($sql);
    }

    public static function search(array $conditions = [], array $fields = [])
    {
        if (empty($conditions) && empty($fields)){
            return static::all();
        }else{
            return self::$db->search(static::$table, $conditions, $fields);
        }
    }
}


?>