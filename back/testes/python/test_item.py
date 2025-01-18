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
        assert post_response.status_code  == 201
        item_ids.append(post_response.json()['id'])
    # Invalidos
    
    # Dados vazio
    post_response = criar(local_endpoint,{})
    print(post_response.json())
    assert post_response.status_code == 400
    # Dados faltantes
    post_response = criar(local_endpoint,{'descricao': 'dado faltante'})
    print(post_response.json())
    assert post_response.status_code == 400
    # Tipo invalido
    post_response = criar(local_endpoint,
        {
            'descricao': 'teste',
            'valor': random_float(),
            'tipo': 'jonathan'
        }
    )
    print(post_response.json())
    assert post_response.status_code == 400
    # Valor negativo
    data['valor'] = -1
    post_response = criar(local_endpoint, data)
    assert post_response.status_code == 400
    pass

# GET / READ
def test_get_item():
    print()
    # Valido por ID
    for item_id in item_ids:
        print(f"ID Item: {item_id}")
        get_response = selecionar(local_endpoint,item_id)
        print(get_response.json())
        assert get_response.status_code == 200
    # Invalido por ID
    get_response = selecionar(local_endpoint,','.join(item_ids), True)
    print(get_response.json())
    assert get_response.status_code == 400

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
        print(put_response.json())
        assert put_response.status_code == 200
        # Invalidos
        
        # Dados vazios
        put_response = atualizar(local_endpoint,{})
        print(put_response.json())
        assert put_response.status_code == 400
        
        # ID faltante
        put_response = atualizar(local_endpoint,{'descricao': 'item atualizado invalido'})
        print(put_response.json())
        assert put_response.status_code == 400
        
        # Tipo invalido
        put_response = atualizar(local_endpoint, {
            'id': item_id,
            'tipo': 'ui ui'
        })
        print(put_response.json())
        assert put_response.status_code == 400
        
        # Valor invalido
        put_response = atualizar(local_endpoint, {
            'id': item_id,
            'valor': -1
        })    
        print(put_response.json())
        assert put_response.status_code == 400
        
        pass

# DELETE / DELETE
def test_delete_item():
    
    # Válido
    delete_response = deletar(local_endpoint, ",".join(map(str, item_ids)))
    print(delete_response.json())
    assert delete_response.status_code == 200
    
    # Invalido ID não existente
    delete_response = deletar(local_endpoint,999)
    print(delete_response.json())
    assert delete_response.status_code == 400
    
    # Invalido
    delete_response = deletar(local_endpoint,'')
    print(delete_response.json())
    assert delete_response.status_code == 400
    pass