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
        $dados_permitidos = ['ativo'];
        // Valida dados passados
        if (!array_keys_exists($data, $dados_obrigatorios)) {
            return criar_mensagem(
                false,
                'Há dados faltantes',
                ['informados' => $data, 'obrigatorios' => $dados_obrigatorios]
            );
        }
        // Retira dados desnecessários
        $data = array_keys_filter($data,array_merge($dados_obrigatorios,$dados_permitidos));
        // Ativo
        if(isset($data['ativo'])){
            $data['ativo'] = is_bool($data['ativo']) ? $data['ativo'] : true;
        }
        // Descrição
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
        // Tipo
        if(!in_array($data['tipo'],$this->enum_valores(static::$enum_tipo))){
            return criar_mensagem(
                false,
                'Tipo invalido, informe um destes: '.implode(', ',$this->enum_valores(static::$enum_tipo)),
                ['informados' => $dados],
            );
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
            $test = strpos($e->getMessage(),'invalid input value for enum');
            if($test){
                return criar_mensagem(false,
                'Tipo invalido, informe um destes: '.implode(', ',$this->enum_valores(static::$enum_tipo)),
                ['detalhes' => $e->getMessage()]);
            }else{
                return criar_mensagem(
                    false, 
                    $e->getMessage(),
                    ['informados' => $data,'status' => 500]);
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
        // Retira dados desnecessários
        $data = array_keys_filter($data,array_merge($dados_obrigatorios,$dados_permitidos));
        // Ativo
        if(isset($data['ativo'])){
            $data['ativo'] = is_bool($data['ativo']) ? $data['ativo'] : true;
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

    public function buscar($data = []){
        [$conditions, $fields, $limit, $offset] = parse_get_params($data);
        $params = [
            'conditions' => $conditions,
            'fields' => $fields,
            'limit' => $limit,
            'offset' => $offset
        ];
        try {
            $result = $this->search($conditions,$fields,$limit,$offset);
            if(empty($result)){
                return criar_mensagem(false,'Nenhum registro encontrado', ['query' => $params]);
            }{
                return criar_mensagem(true, 'Busca realizada com sucesso', ['lista' => $result]);
            }
        } catch (Exception $e) {
            return criar_mensagem(
                false, 
                'Houve um erro ao realizar busca',
                [
                    'detalhes' => $e->getMessage(),
                    'params' => json_encode($data)
                ]
            );
        }
    }

}