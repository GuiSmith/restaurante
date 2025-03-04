<?php

require_once 'Database.php';

class Log {
    private static $instance = null;
    private static $table = 'log';
    private static $db;

    function __construct(){
        static::$db = Database::getInstance();
    }

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new Log();
        }
        return self::$instance;
    }

    public static function insert($table,$operation,$data, $token = null){
        $dados_obrigatorios = ['tabela','operacao','registro'];
        //Retirando Token do registro
        //Criando dados para inserir
        $dados = [
            'tabela' => $table,
            'operacao' => $operation,
            'registro' => json_encode($data)
        ];
        //Definindo conteúdo de registros
        if($operation != 'DELETE'){
            $dados['registro'] = json_encode($data);
        }else{
            //Se não encontrou registros com os IDs a serem deletados, não inserir logs
            $rows = [];
            foreach ($data['id'] as $id) {
                $rows[] = self::$db->fetch($table,['id' => (int)$id]);
            }
            if (count($rows) > 0){
                $dados['registro'] = json_encode($rows);
            }else{
                return;
            }
        }
        //Inserindo
        try {
            self::$db->insert(static::$table,$dados);
        } catch (Exception $e) {
            echo "Erro ao inserir log: ".$e->getMessage();
        }

    }

    public function all(){
        $sql = "SELECT * FROM " . static::$table;
        //var_dump(self::$db);
        return self::$db->fetchAll($sql);
    }

    public function search(array $conditions = [], array $fields = [])
    {
        //Se condições E campos forem vazios, chamar por ALL
        if (empty($conditions) && empty($fields)){
            $search = $this->all();
        }else{
            $search = self::$db->search(static::$table, $conditions, $fields);
        }
        return $search;
    }

    // public function buscar($data){
    //     [$conditions, $fields, $limit, $offset] = parse_get_params($data);
    //     try {
    //         $result = self::$db->search(static::$table,$conditions,$fields, $limit, $offset);
    //         return criar_mensagem(true, 'Busca realizada com sucesso', $result);
    //     } catch (Exception $e) {
    //         return criar_mensagem(
    //             false, 
    //             'Houve um erro ao realizar busca',
    //             [
    //                 'detalhes' => $e->getMessage(),
    //                 'params' => json_encode($data)
    //             ]
    //         );
    //     }
    // }

}