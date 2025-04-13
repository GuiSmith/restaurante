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
            }
        }
        if(!self::$log){
            self::$log = Log::getInstance();
        }
    }

    public function insert(array $data): bool|string
    {
        // Atualizando campos ENUM para maiúsculos
        if (isset($data['status'])) {
            $data['status'] = strtoupper($data['status']);
        }
        
        // Lidando com tokens
        $token = $data['token'] ?? null;
        if ($token) {
            unset($data['token']);
        }

        // Inserindo registro
        $insert = self::$db->insert(static::$table, $data);

        // Registrando log
        if ($insert) static::$log->insert(static::$table, 'INSERT', $data, $token);
        return $insert;
    }

    public function update(array $data)
    {
        // Atualizando campos ENUM para maiúsculos
        if(isset($data['status'])) {
            $data['status'] = strtoupper($data['status']);
        }

        // Lidando com tokens
        $token = $data['token'] ?? null;
        if($token){
            if(isset($data['login']) and $data['login']){
                unset($data['login']);
            }else{
                unset($data['token']);
            }
        }

        // Atualizando registro
        $linhas_afetadas = self::$db->update(static::$table, $data);
        
        // Registrando log
        if($linhas_afetadas > 0) static::$log->insert(static::$table,'UPDATE',$data, $token);
        return $linhas_afetadas;
    }

    public function delete($data = []): bool|PDOStatement
    {
        // Iniciando transação
        self::$db->beginTransaction();
        
        try {
            // Lidando com tokens
            $token = $data['token'] ?? null;
            if ($token) {
            unset($data['token']);
            }

            // Registrando log
            static::$log->insert(static::$table, 'DELETE', $data, $token);

            // Deletando registro
            $linhas_afetadas = self::$db->delete(static::$table, $data['id']);

            // Verificando se alguma linha foi afetada
            if ($linhas_afetadas === 0) {
                throw new Exception("Nenhum registro foi deletado.");
            }

            // Commit se tudo der certo
            self::$db->commit();
            return $linhas_afetadas;
        } catch (Exception $e) {
            // Rollback em caso de erro
            self::$db->rollBack();
            throw $e;
        }
    }

    public function all()
    {
        $sql = "SELECT * FROM " . static::$table;
        //var_dump(self::$db);
        return self::$db->fetchAll($sql);
    }

    public function fetch(array $param = []){
        //Se vazio
        if(empty($param) || empty(array_values($param)[0])){
            return criar_mensagem(false,"Nenhum dado foi enviado para pesquisa",['dados' => $param]);
        }
        try {
            $result = self::$db->fetch(static::$table,$param);
            return $result ?? criar_mensagem(
                false,
                'Nenhum dado encontrado com esta chave',
                ['chave' => $param]
            );
        } catch (PDOException $e) {
            return criar_mensagem(
                false,
                self::$db->db_catch_to_string($e),
                ['detalhes' => $e->getMessage()]
            );
        }
    }

    public function search(array $conditions = [], array $fields = [], int $limit = null, int $offset = null, array $order_by = null, array $like = null)
    {
        return self::$db->search(static::$table,$conditions,$fields,$limit,$offset,$order_by, $like);
    }

    // Retorna o status de uma comanda específica
    public function status_comanda($id_comanda){
        $search = self::$db->search('comanda',['id' => $id_comanda], ['status']);
        return $search[0]['status'] ?? null;
    }

    // Verifica se uma comanda específica tem itens em aberto
    public function itens_abertos($id_comanda){
        $sql = "SELECT verificar_itens_em_aberto($id_comanda);";
        $search = self::$db->query($sql);
        return $search->fetch()['verificar_itens_em_aberto'];
    }

    //Retorna os valores de um tipo ENUM do banco de dados
    public function enum_valores($type){
        $sql = "SELECT enum_range(NULL::$type)";
        $values_string = self::$db->query($sql)->fetch()['enum_range'];
        return explode(',', trim($values_string, '{}'));
    }
}

?>