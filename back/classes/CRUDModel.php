<?php

require_once "Database.php";
require_once __DIR__."/../functions.php";
require_once 'Log.php';

class CRUDModel
{
    protected static $db;
    protected static $table; // Nome da tabela associada
    protected static $log;

    public function __construct($debug = false)
    {
        if (!self::$db) {
            self::$db = Database::getInstance();
            if ($debug) {
                echo "<p>construtor de CRUDModel</p>";
                var_dump(self::$db);
            }
        }
        if(!self::$log){
            self::$log = Log::getInstance();
        }
    }

    public function insert(array $data): bool|string
    {
        $insert = self::$db->insert(static::$table, $data);
        if($insert) static::$log->insert(static::$table,'INSERT',$data);
        return $insert;
    }

    public function update(array $data)
    {
        $linhas_afetadas = self::$db->update(static::$table, $data);
        if($linhas_afetadas > 0) static::$log->insert(static::$table,'UPDATE',$data);
        return $linhas_afetadas;
    }

    public function delete($ids = []): bool|PDOStatement
    {
        static::$log->insert(static::$table,'DELETE',['ids' => $ids]);
        return self::$db->delete(static::$table, $ids);
    }

    public function all()
    {
        $sql = "SELECT * FROM " . static::$table;
        //var_dump(self::$db);
        $search = self::$db->fetchAll($sql);
        $log_dados['linhas'] = count($search);
        static::$log->insert(static::$table,'SELECT',$log_dados);
        return $search;
    }

    public function search(array $conditions = [], array $fields = [])
    {
        //Se condições E campos forem vazios, chamar por ALL
        if (empty($conditions) && empty($fields)){
            $search = $this->all();
        }else{
            $search = self::$db->search(static::$table, $conditions, $fields);
            $log_dados['condicoes'] = json_encode($conditions);
            $log_dados['linhas'] = count($search);
            static::$log->insert(static::$table,'SELECT',$log_dados);
        }
        return $search;
    }
}


?>