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

    public static function insert($table,$operation,$data){
        $dados_obrigatorios = ['tabela','operacao','registro'];
        //Retirando Token do registro
        if(in_array('token',$data)){
            $usuario = static::$db->search('usuario',['token' => $data['token']],['id'])[0];
            $id_usuario = $usuario['id'];
        }else{
            $id_usuario = null;
        }
        //Criando dados para inserir
        $dados = [
            'tabela' => $table,
            'operacao' => $operation,
            'registro' => json_encode($data),
            'id_usuario' => $id_usuario
        ];
        //Definindo conteúdo de registros
        if($operation != 'DELETE'){
            $dados['registro'] = json_encode($data);
        }else{
            //Se não encontrou registros com os IDs a serem deletados, não inserir logs
            $rows = [];
            foreach ($data['ids'] as $id) {
                $rows[] = self::$db->search($table,['id' => (int)$id]);
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

}