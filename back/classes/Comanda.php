<?php
require_once 'CRUDModel.php';

class Comanda extends CRUDModel
{
    protected static $table = 'comanda';

    public function __construct($debug = false)
    {
        parent::__construct($debug);
        if ($debug) {
            echo "<p>Construtor de Comanda</p>";
        }
    }

    public function abrir($data = [])
    {
        $dados_permitidos = ['token'];
        // Valida dados passados
        if(!array_keys_exists($data, $dados_permitidos)){
            return criar_mensagem(
                false, 
                'Há dados faltantes',
                ['informados' => $data,'permitidos'=>$dados_permitidos]
                );
        }
        //Definindo status de comanda para aberta
        $data['status'] = 'aberta';
        try {
            $id = $this->insert($data);
            return criar_mensagem(true, 'Comanda aberta com sucesso.', ['id' => $id]);
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage(),['status'=>500]);
        }
    }

    public function fechar($data)
    {
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
        $itens_abertos = $this->itens_abertos($data['id']); 
        if($itens_abertos){
            return criar_mensagem(false, 'Nao e possivel fechar comanda pois ha itens em aberto');
        }
        try {
            $data['status'] = 'fechada';
            $linhas_afetadas = $this->update($data);
            if($linhas_afetadas > 0){
                return criar_mensagem(true,'Comanda fechada');
            }else{
                return criar_mensagem(false,'Comanda não encontrada: '.$data['id'],['status'=>404]);
            }
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function deletar($data)
    {
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
        [$conditions, $fields, $limit, $offset, $order_by] = parse_get_params($data);
        $params = [
            'conditions' => $conditions,
            'fields' => $fields,
            'limit' => $limit,
            'offset' => $offset,
            'order_by' => $order_by,
        ];
        try {
            $result = $this->search($conditions,$fields,$limit,$offset,$order_by);
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