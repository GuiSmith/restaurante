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

    public function criar($data)
    {
        $dados_obrigatorios = ['status'];
        $data = array_intersect_key($data, array_flip($dados_obrigatorios));

        // Checar se todos os campos obrigatórios estão presentes
        if (!array_keys_exists($data, $dados_obrigatorios)) {
            return criar_mensagem(
                false,
                "Informe os campos obrigatórios: " . implode(', ', $dados_obrigatorios) . "."
            );
        }

        try {
            $id = $this->insert($data);
            return criar_mensagem(true, 'Comanda criada com sucesso.', ['id' => $id]);
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function atualizar($data)
    {
        $dados_permitidos = ['status', 'acrescimos', 'descontos', 'id'];
        $data = array_intersect_key($data, array_flip($dados_permitidos));

        //ID para poder atualizar registro
        if (!isset($data['id'])) {
            return criar_mensagem(false, 'Informe o ID para atualizar a comanda.');
        }

        try {
            $linhas_afetadas = $this->update($data);
            return criar_mensagem(
                true,
                $linhas_afetadas > 0 ? 'Comanda atualizada com sucesso.' : 'Nenhuma alteração realizada.',
                ['rows' => $linhas_afetadas]
            );
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }

    public function deletar(string $ids)
    {
        if (empty($ids)) {
            return criar_mensagem(false, 'ID é obrigatório para deletar comandas.');
        }

        try {
            $id_array = explode(',',$ids); //Criando array de IDs
            if($this->delete($id_array)){
                return criar_mensagem(true,"Comandas deletadas com sucesso");
            }else{
                return criar_mensagem(false,"Nenhuma comanda encontrada com o(s) ID(s) informado(s)");
            }
        } catch (Exception $e) {
            return criar_mensagem(false, $e->getMessage());
        }
    }
}
