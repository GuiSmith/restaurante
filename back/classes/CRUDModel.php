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
        if(isset($data['status'])) {
            $data['status'] = strtoupper($data['status']);
        }
        $insert = self::$db->insert(static::$table, $data);
        if($insert) static::$log->insert(static::$table,'INSERT',$data);
        return $insert;
    }

    public function update(array $data)
    {
        if(isset($data['status'])) {
            $data['status'] = strtoupper($data['status']);
        }
        $linhas_afetadas = self::$db->update(static::$table, $data);
        if($linhas_afetadas > 0) static::$log->insert(static::$table,'UPDATE',$data);
        return $linhas_afetadas;
    }

    public function delete($data = []): bool|PDOStatement
    {
        static::$log->insert(static::$table,'DELETE',$data);
        return self::$db->delete(static::$table, $data['id']);
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

    public function search(array $conditions = [], array $fields = [])
    {
        //Se condições E campos forem vazios, chamar por ALL
        if (empty($conditions) && empty($fields)){
            return $this->all();
        }else{
            //Mudando valores de status para maiúsculos
            if(array_key_exists('status',$conditions)){
                $conditions['status'] = strtoupper($conditions['status']);
            }
            //Verificando se há algum valor nulo nas condições passadas
            if (in_array('',array_values($conditions))) {
                return criar_mensagem(
                    false, 
                    'Impossivel realizar pesquisa usando filtros com valores nulos',
                    ['filtros' => $conditions]
                );
            }
            try {
                $result = self::$db->search(static::$table, $conditions, $fields);
                return $result ?? criar_mensagem(
                    false,
                    'Nenhum dado encontrado com os filtros mencionados',
                    [
                        'filtros' => $conditions,
                        'colunas' => $fields
                    ]
                );
            } catch (PDOException $e) {
                return criar_mensagem(
                    false,
                    self::$db->db_catch_to_string($e),
                    [
                            'detalhes' => $e->getMessage(),
                            'filtros' => $conditions,
                            'colunas' => $fields,
                            'status' => 500
                    ]
                );
            }            
        }
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