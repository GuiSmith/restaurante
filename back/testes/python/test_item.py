import os
from config import *

local_endpoint = '/item.php'
tipos = ['BEBIDA','PRATO']

item_ids = []

os.system('clear')

# POST / CREATE
def test_post_item():
    print()
    
    # Valido
    data = {
        'descricao': 'test_post',
        'valor': random_float(),
    }
    for tipo in tipos:
        # Criando
        data['tipo'] = tipo
        post_response = criar(local_endpoint,data)
        dados = post_response.json()
        assert dados['ok'] is True
        item_ids.append(dados['id'])

    # Invalidos
    
    # Dados vazio
    post_response = criar(local_endpoint,{})
    dados = post_response.json()
    print(dados)
    assert dados['ok'] is False

    # Dados faltantes
    post_response = criar(local_endpoint,{'descricao': 'dado faltante'})
    dados = post_response.json()
    print(dados)
    assert dados['ok'] is False

    # Tipo invalido
    post_response = criar(local_endpoint,
        {
            'descricao': 'teste',
            'valor': random_float(),
            'tipo': 'jonathan'
        }
    )
    dados = post_response.json()
    print(dados)
    assert dados['ok'] is False

    # Valor negativo
    data['valor'] = -1
    post_response = criar(local_endpoint, data)
    dados = post_response.json()
    print(dados)
    assert dados['ok'] is False
    pass

# GET / READ
def test_get_item():
    print()
    # Valido por ID
    for item_id in item_ids:
        print(f"ID Item: {item_id}")
        get_response = selecionar(local_endpoint,item_id)
        dados = get_response.json()
        print(dados)
        assert get_response.status_code == 200
        
    # Invalido por ID
    ids = ','.join(item_ids)
    print(f"IDs: {ids}")
    get_response = selecionar(local_endpoint,ids, True)
    print(get_response.json())
    assert get_response.status_code != 200
    
    # Valido, sem ID
    get_response = selecionar(local_endpoint)
    # print(get_response.json())
    assert get_response.status_code == 200
    
    # Invalido, ID não encontrado
    get_response = selecionar(local_endpoint,999)
    print(get_response)
    assert get_response.status_code == 204
    pass

# PUT / UPDATE
def test_update_item():
    print()
    # Valido
    for item_id in item_ids:
        print(f"ID item: {item_id}")
        
        # Valido
        data = {
            'id': item_id,
            'descricao': 'item atualizado'
        }
        put_response = atualizar(local_endpoint,data)
        dados = put_response.json()
        print(dados)
        assert dados['ok'] is True
        
        # Invalidos
        
        # ID não encontrado
        put_response = atualizar(local_endpoint,{
            'id': 999,
            'valor': 5
        })
        dados = put_response.json()
        print(dados)
        assert dados['ok'] is False
        
        # Dados vazios
        put_response = atualizar(local_endpoint,{})
        dados = put_response.json()
        print(dados)
        assert dados['ok'] is False
        
        # ID faltante
        put_response = atualizar(local_endpoint,{'descricao': 'item atualizado invalido'})
        dados = put_response.json()
        print(dados)
        assert dados['ok'] is False
        
        # Tipo invalido
        put_response = atualizar(local_endpoint, {
            'id': item_id,
            'tipo': 'ui ui'
        })
        dados = put_response.json()
        print(dados)
        assert dados['ok'] is False
        
        # Valor invalido
        put_response = atualizar(local_endpoint, {
            'id': item_id,
            'valor': -1
        })    
        dados = put_response.json()
        print(dados)
        assert dados['ok'] is False
        
        pass

# DELETE / DELETE
def test_delete_item():
    
    # Válido
    delete_response = deletar(local_endpoint, ",".join(map(str, item_ids)))
    dados = delete_response.json()
    print(dados)
    assert dados['ok'] is True
    
    # Invalido ID não existente
    delete_response = deletar(local_endpoint,999)
    dados = delete_response.json()
    print(dados)
    assert dados['ok'] is False
    
    # Invalido
    delete_response = deletar(local_endpoint,'')
    dados = delete_response.json()
    print(dados)
    assert dados['ok'] is False    
    pass