<?php

/* Função que valida:
1 - Verificar se um array possui todas as chaves obrigatórias
2 - Se o array está vazio
3 - Se o array é nulo
*/
function array_keys_exists($data, $dados_obrigatorios)
{
    foreach ($dados_obrigatorios as $dado_obrigatorio) {
        if (!array_key_exists($dado_obrigatorio, $data)) {
            return false;
        }
    }
    return true;
}

// Função que retira todas as chaves que não são permitidas
function array_keys_filter($data, $dados_permitidos)
{
    if(empty($data)){
        return [];
    }else{
        return array_intersect_key($data, array_flip($dados_permitidos));
    }
}

function parse_get_params(array $url_query)
{
    $conditions = [];
    $fields = [];
    $limit = null;
    $offset = null;
    $order_by = null;

    if (isset($url_query['fields'])) {
        $fields = explode(',', $url_query['fields']);
    }

    if (isset($url_query['limit'])) {
        $limit = (int)$url_query['limit'];
    }

    if (isset($url_query['offset'])) {
        $offset = (int)$url_query['offset'];
    }

    if(isset($url_query['order_by'])){
        $order_by = $url_query['order_by'];
    }

    foreach ($url_query as $key => $value) {
        if (!in_array($key, ['fields', 'limit', 'offset', 'order_by'])) {
            $conditions[$key] = $value;
        }
    }

    return [$conditions, $fields, $limit, $offset, $order_by];
}

function criar_mensagem(bool $ok, string $mensagem, array $dados = [])
{
    $array = ['ok' => $ok, 'mensagem' => $mensagem];
    if(!empty($dados)){
        $array = array_merge($array, $dados);
    }
    return $array;
}

function normalizar_valor($valor)
{
    // Remove espaços extras
    $valor = trim($valor);

    // Substitui vírgula por ponto, caso o formato seja brasileiro
    $valor = str_replace(',', '.', $valor);

    // Tenta converter para número flutuante
    $numero = filter_var($valor, FILTER_VALIDATE_FLOAT);

    // Se não for um número válido, retorna erro ou valor padrão
    if ($numero === false) {
        throw new InvalidArgumentException("O valor '$valor' não é válido.");
    }

    // Formata para 2 casas decimais
    return number_format($numero, 2, '.', '');
}