<?php

require_once 'CRUDModel.php';

class Pagamento extends CRUDModel
{
    protected static $table = 'pagamento';
    protected static $formas_pagamento = ['pix','debito','credito','boleto','transferencia','cheque'];
    public function __construct($debug = false)
    {
        parent::__construct($debug);
        if ($debug) {
            echo "<p>Constructor de usuario</p>";
        }
    }

    public function criar($data)
    {
        $dados_obrigatorios = ['id_comanda', 'forma_pagamento', 'valor'];
        $dados_permitidos = ['token'];
        //Tratando quando dados não são enviados
        if (empty($data)){
            return criar_mensagem(false,'Nenhum dado foi enviado, informe dados obrigatorios: '
                .implode(', ', $dados_obrigatorios)
            );
        }
        //Retirando somente as chaves necessárias
        $data = array_intersect_key($data, array_flip(array_merge($dados_obrigatorios, $dados_permitidos)));
        //Checando vazio
        if (!array_keys_exists($data, $dados_obrigatorios)) {
            return criar_mensagem(
                false, 
                "Informe os campos obrigatorios: "
                .implode(', ', $dados_obrigatorios)
                .". Voce informou: "
                .implode(", ", array_keys($data)));
        }
        //Forma pagamento
        if (!in_array($data['forma_pagamento'],static::$formas_pagamento)) {
            return criar_mensagem(
                false,
                'Tipo invalido, informe um dos seguintes: '
                .implode(', ',static::$formas_pagamento)
                .". Voce informou "
                .$data['forma_pagamento']
            );
        }else{
            $data['forma_pagamento'] = strtoupper($data['forma_pagamento']);
        }
        //Valor
        if ($data['valor'] < 0) {
            return criar_mensagem(false, 'valor invalido, informe um valor positivo. Ex: 00.00');
        } else {
            //Formata o valor para retornar como 00.00
            $data['valor'] = normalizar_valor($data['valor']);
        }
        if($this->status_comanda($data['id_comanda']) != 'FECHADA'){
            return criar_mensagem(false, 'Comanda nao esta fechada, feche-a para continuar');
        }
        //Criando item
        try {
            $id = $this->insert($data);
            return criar_mensagem(true,'Pagamento registrado com sucesso',['id' => $id]);
        } catch(Exception $e){
            if($e->getCode() == 'P0001'){
                return criar_mensagem(false,'Pagamento ultrapassa valor aberto da comanda');
            }else{
                return criar_mensagem(false, $e->getMessage());
            }
        }
    }

    public function deletar($data){
        $dados_obrigatorios = ['id'];
        $dados_permitidos = ['token'];
        //Tratando vazio
        if (empty($data)){
            return criar_mensagem(false,'ID e necessario para deletar usuarios');
        }
        //Retirando dados necessários
        $data = array_intersect_key($data,array_flip(array_merge($dados_obrigatorios,$dados_permitidos)));
        //Verificando IDs
        if(!isset($data['id'])){
            return criar_mensagem(false,'ID e necessario para deletar usuarios');
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
            return match ($e->getCode()) {
                23503 => criar_mensagem(false, "Nao e possivel deletar $this->table pois outros registros dependem deste"),
                default => criar_mensagem(false, $e->getMessage()),
            };
        }
    }
}