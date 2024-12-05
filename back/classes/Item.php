<?php

require_once 'CRUDModel.php';

class Item extends CRUDModel
{
    protected static $table = 'item';
    protected $tipos = ['bebida', 'prato'];

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
        //Retirando somente as chaves necessárias
        $data = array_intersect_key($data, array_flip($dados_obrigatorios));
        //Checando vazio
        if (!array_keys_exists($data, $dados_obrigatorios)) {
            return criar_mensagem(
                false, 
                "Informe os campos obrigatorios: "
                .implode(', ', $dados_obrigatorios)
                .". Voce informou: "
                .implode(", ", array_keys($data)));
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
        //Tipo
        if (!in_array($data['tipo'], $this->tipos)) {
            return criar_mensagem(false, 'tipo invalido, informe um destes: ' . implode(',', $this->tipos));
        }
        //Criando item
        try {
            $id = $this->insert($data);
            return criar_mensagem(true,'Item criado com sucesso',['id' => $id]);
        } catch(Exception $e){
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function atualizar($data){
        $dados_opcionais = ['descricao','valor','tipo','ativo','id'];
        $dados_obrigatorios = ['id'];
        //Extraindo somente dados necessários
        $data = array_intersect_key($data,array_flip($dados_opcionais));
        //Verificando ID
        if(!isset($data['id'])){
            return criar_mensagem(false,'informe o ID para atualizar itens');
        }
        //Descrição
        if(isset($data['descricao']) && empty($data['descricao'])){
            return criar_mensagem(false,'Descricao invalida');
        }
        //Valor
        if(isset($data['valor']) && $data['valor'] < 0){
            return criar_mensagem(false,'valor invalido');
        }
        //Tipo
        if(isset($data['tipo']) && !in_array($data['tipo'],$this->tipos)){
            return criar_mensagem(false,'tipo invalido, informe um destes: '.implode(',',$this->tipos));
        }
        //Atualizando
        try{
            $linhas_afetadas = $this->update($data);
            return criar_mensagem(true,'item atualizado com sucesso',['rows' => $linhas_afetadas]);
        } catch(Exception $e){
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function deletar(string $ids){
        //Verificando IDs
        if(empty($ids)){
            return criar_mensagem(false,'ID e necessario para deletar itens');
        }
        //Deletando
        try{
            $ids = explode(',',$ids);
            $linhas_afetadas = $this->delete($ids);
            return criar_mensagem(true,'itens deletados com sucesso',['rows' => $linhas_afetadas]);
        } catch(Exception $e){
            return criar_mensagem(false,$e->getMessage());
        }
    }
}