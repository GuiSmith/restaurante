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
        //Retirando dados desnecessários
        $data = !empty($data) ? array_intersect_key($data,array_flip($dados_permitidos)) : [];
        //Definindo status de comanda para aberta
        $data['status'] = 'ABERTA';
        try {
            $id = $this->insert($data);
            return criar_mensagem(true, 'Comanda aberta com sucesso.', ['id' => $id]);
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function fechar($data)
    {
        $dados_permitidos = ['id','token'];
        $data = !empty($data) ? array_intersect_key($data, array_flip($dados_permitidos)) : [];
        //ID para poder atualizar registro
        if (!isset($data['id'])) {
            return criar_mensagem(false, 'Informe o ID para fechar a comanda.');
        }
        if($this->itens_abertos($data['id'])){
            return criar_mensagem(false, 'Nao e possivel fechar comanda pois ha itens em aberto');
        }
        try {
            $data['status'] = 'FECHADA';
            $linhas_afetadas = $this->update($data);
            if($linhas_afetadas > 0){
                return criar_mensagem(true,'Comanda fechada');
            }else{
                return criar_mensagem(false,'Comanda não encontrada: '.$data['id']);
            }
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function deletar($data)
    {
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
            if($e->getCode() == 23503){
                return criar_mensagem(false, "Nao e possivel deletar $this->table pois outros registros dependem deste");
            }else{
                return criar_mensagem(false, $e->getMessage());
            }
        }
    }
}
