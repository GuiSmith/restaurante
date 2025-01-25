<?php

require_once 'CRUDModel.php';

class ItemComanda extends CRUDModel
{
    protected static $table = 'item_comanda';

    protected static $status = ['cadastrado','confirmado','pronto','entregue'];

    public function __construct($debug = false)
    {
        parent::__construct($debug);
        if ($debug) {
            echo "<p>Constructor de item comanda</p>";
        }
    }

    public function criar($data)
    {
        $dados_obrigatorios = ['id_item', 'id_comanda', 'quantidade'];
        $dados_permitidos = ['token','descontos','isento','status'];
        // Valida dados passados
        if(array_keys_exists($data,$dados_obrigatorios)){
            return criar_mensagem(
                false,
                'Ha dados faltantes',
            ['obrigatorios' => $dados_obrigatorios, 'permitidos' => $dados_permitidos]
            );
        }
        // ID Item
        if (empty($data['id_item']) || !is_numeric($data['id_item'])) {
            return criar_mensagem(false, 'ID do item invalido');
        }
        // ID Comanda
        if (empty($data['id_comanda']) || !is_numeric($data['id_comanda'])) {
            return criar_mensagem(false, 'ID da comanda invalido');
        }
        // Quantidade
        if (empty($data['quantidade']) || $data['quantidade'] <= 0) {
            return criar_mensagem(false, 'Quantidade invalida, deve ser maior que 0');
        }
        // Status (opcional)
        if (isset($data['status'])) {
            if(empty($data['status']) || !in_array($data['status'],static::$status)){
                return criar_mensagem(false, 'Status invalido, informe um destes: '.implode(', ',array_map('strtolower',static::$status)));
            }
        }
        // Descontos (opcional)
        if (isset($data['descontos'])) {
            if($data['descontos'] < 0){
                return criar_mensagem(false, 'Desconto invalido, deve ser maior ou igual a 0');
            }else{
                $data['descontos'] = normalizar_valor($data['descontos']);
            }
        }
        // Isento (opcional)
        if (isset($data['isento']) && !is_bool($data['isento'])) {
            return criar_mensagem(false, 'O campo "isento" deve ser um valor booleano');
        }

        // Criando o item comanda
        try {
            $id = $this->insert($data);
            if ($id != 0 && is_numeric($id)){
                return criar_mensagem(true, 'Item inserido na comanda ID '.$data['id_comanda'].' com sucesso', ['id' => $id]);
            }else{
                return criar_mensagem(false, 'Item nao inserido na comanda');
            }
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function atualizar($data)
    {
        $dados_obrigatorios = ['id'];
        $dados_permitidos = ['token','quantidade', 'status', 'descontos', 'isento'];
        // Valida dados passados
        if(array_keys_exists($data,$dados_obrigatorios)){
            return criar_mensagem(
                false,
                'Ha dados faltantes',
                ['obrigatorios' => $dados_obrigatorios, 'permitidos' => $dados_permitidos]
            );
        }
        // Quantidade
        if (isset($data['quantidade']) && $data['quantidade'] <= 0) {
            return criar_mensagem(false, 'Quantidade invalida, deve ser maior que 0');
        }
        // Status (opcional)
        if (isset($data['status'])) {
            $data['status'] = strtolower($data['status']);
            if (empty($data['status']) || !in_array($data['status'],static::$status)){
                return criar_mensagem(false, 'Status invalido, informe um destes: '.implode(', ',array_map('strtolower',static::$status)));
            }
        }
        // Descontos
        if (isset($data['descontos'])) {
            if ($data['descontos'] < 0) {
                return criar_mensagem(false, 'Desconto invalido, deve ser maior ou igual a 0');
            }else{
                $data['descontos'] = normalizar_valor($data['descontos']);
            }
        }
        // Isento
        if (isset($data['isento']) && !is_bool($data['isento'])) {
            return criar_mensagem(false, 'O campo "isento" deve ser um valor booleano');
        }

        // Atualizando item comanda
        try {
            $linhas_afetadas = $this->update($data);
            if ($linhas_afetadas > 0){
                return criar_mensagem(true, 'Item atualizado com sucesso');
            }else{
                return criar_mensagem(false, 'Item nao atualizado');
            }
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function deletar($data)
    {
        $dados_obrigatorios = ['id'];
        $dados_permitidos = ['token'];
        // Validando dados passados
        if(array_keys_exists($data,$dados_obrigatorios)){
            return criar_mensagem(
                false,
                'Ha dados faltantes',
                ['obrigatorios' => $dados_obrigatorios, 'permitidos' => $dados_permitidos]
            );
        }
        //Deletando
        try{
            $data['id'] = explode(',',$data['id']);
            if($this->delete($data)){
                return criar_mensagem(true,'Registro deletado com sucesso');
            }else{
                return criar_mensagem(false,'Nenhum registro encontrado com ids fornecidos');
            }
        } catch(Exception $e){
            if($e->getCode() == 23503){
                return criar_mensagem(false, "Nao e possivel deletar $this->table pois outros registros dependem deste");
            }else{
                return criar_mensagem(false, $e->getMessage());
            }
        }
    }
}