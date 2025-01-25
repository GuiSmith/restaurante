<?php

require_once 'CRUDModel.php';

class Item extends CRUDModel
{
    protected static $table = 'item';

    protected static $enum_tipo = 'item_tipos';

    public function __construct($debug = false)
    {
        parent::__construct($debug);
        if ($debug) {
            echo "<p>Constructor de usuario</p>";
        }
    }

    public function criar($data)
    {
        $dados_obrigatorios = ['descricao', 'valor', 'tipo'];
        // Valida dados passados
        if (!array_keys_exists($data, $dados_obrigatorios)) {
            return criar_mensagem(
                false,
                'Há dados faltantes',
                ['informados' => $data, 'obrigatorios' => $dados_obrigatorios]
            );
        }
        //Descrição
        if (empty($data['descricao'])) {
            return criar_mensagem(false, 'Descricao invalida');
        }
        //Valor
        if ($data['valor'] < 0) {
            return criar_mensagem(false, 'valor invalido, informe um valor positivo. Ex: 00.00');
        } else {
            //Formata o valor para retornar como 00.00
            $data['valor'] = normalizar_valor($data['valor']);
        }
        //Criando item
        try {
            $id = $this->insert($data);
            return criar_mensagem(
                true,
                'Item criado com sucesso',
                ['id' => $id],
            );
        } catch(Exception $e){
            if($e->getCode() == '22P02'){
                return criar_mensagem(false, 'Tipo invalido, informar um destes: '.implode(', ',$this->enum_valores(static::$enum_tipo)));
            }else{
                return criar_mensagem(false, $e->getMessage(),['status'=>500]);
            }
        }
    }

    public function atualizar($data){
        $dados_permitidos = ['descricao','valor','tipo','ativo','id'];
        $dados_obrigatorios = ['id'];
        // Valida dados passados
        if(!array_keys_exists($data, $dados_obrigatorios)){
            return criar_mensagem(
                false, 
                'Há dados faltantes',
                ['informados' => $data, 'obrigatorios' => $dados_obrigatorios, 'permitidos' => $dados_permitidos]
            );
        }
        //Descrição
        if(isset($data['descricao']) && empty($data['descricao'])){
            return criar_mensagem(false,'Descricao invalida');
        }
        //Valor
        if(isset($data['valor']) && $data['valor'] < 0){
            return criar_mensagem(false,'valor invalido');
        }
        //Atualizando
        try{
            $linhas_afetadas = $this->update($data);
            if ($linhas_afetadas == 0) {
                return criar_mensagem(false, 'Nenhum item encontrado com o id fornecido', ['status' => 404]);
            }else{
                return criar_mensagem(true,'Item atualizado',['linhas_afetadas' => $linhas_afetadas]);
            }
        } catch(Exception $e){
            $test = strpos($e->getMessage(),'invalid input value for enum');
            if($test){
                return criar_mensagem(false,
                'Tipo invalido, informe um destes: '.implode(', ',$this->enum_valores(static::$enum_tipo)),
                ['detalhes' => $e->getMessage()]);
            }else{
                return criar_mensagem(false, $e->getMessage(),['detalhes' => $test,'status' => 500]);
            }
        }
    }
    public function deletar($data){
        $dados_obrigatorios = ['id'];
        $dados_permitidos = ['token'];
        // Valida dados passados
        if(!array_keys_exists($data, $dados_obrigatorios)){
            return criar_mensagem(
                false, 
                'Há dados faltantes',
                ['informados' => $data, 'obrigatorios' => $dados_obrigatorios, 'permitidos' => $dados_permitidos]
            );
        }
        //Deletando
        try{
            $data['id'] = explode(',',$data['id']);
            if($this->delete($data)){
                return criar_mensagem(true,'Registro deletado com sucesso');
            }else{
                return criar_mensagem(false,'Nenhum registro encontrado com ids fornecidos',['status'=>404]);
            }
        } catch(Exception $e){
            if($e->getCode() == 23503){
                return criar_mensagem(false, "Nao e possivel deletar $this->table pois outros registros dependem deste");
            }else{
                return criar_mensagem(false, $e->getMessage(),['status'=>500]);
            }
        }
    }

}