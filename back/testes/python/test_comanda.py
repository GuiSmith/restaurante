import os 
from config import *

local_endpoint = '/comanda.php'
comanda_id = 0

os.system('clear')

# POST / CREATE
def test_post_comanda():
    print()
    global comanda_id
    
    # Valido
    post_response = criar(local_endpoint)
    dados = post_response.json()
    print(dados)
    comanda_id = dados['id']
    assert dados['ok'] is True
    pass
    
# GET / READ
def test_get_comanda():
    print()
    
    # Valido por ID
    print(f"ID Comanda: {comanda_id}")
    get_response = selecionar(local_endpoint,comanda_id)
    dados = get_response.json()
    print(dados)
    assert get_response.status_code == 200
    
    # Valido sem ID
    print(f"ID Comanda: nenhum")
    get_response = selecionar(local_endpoint)
    dados = get_response.json()
    print(dados)
    assert get_response.status_code == 200
    
    # Invalido por ID
    id_invalido = 999
    print(f"ID Comanda: {id_invalido}")
    get_response = selecionar(local_endpoint,id_invalido)
    print(get_response)
    assert get_response.status_code == 204
    
    pass

def test_update_comanda():
    print()
        
    # Valido
    print(f"ID Comanda: {comanda_id}")
    put_response = atualizar(local_endpoint,{'id': comanda_id})
    dados = put_response.json()
    print(dados)
    assert dados['ok'] is True
    
    # Invalido, sem passar ID
    print(f"ID Comanda: nenhum")
    put_response = atualizar(local_endpoint)
    dados = put_response.json()
    print(dados)
    assert dados['ok'] is False
    
    # Invalido, id não encontrado
    id_invalido = 999
    print(f"ID Comanda: {id_invalido}")
    put_response = atualizar(local_endpoint,{'id': id_invalido})
    dados = put_response.json()
    print(dados)
    assert dados['ok'] is False
    
def test_delete_comanda():
    print()
    
    # Valido
    print(f"ID Comanda: {comanda_id}")
    delete_response = deletar(local_endpoint,comanda_id)
    dados = delete_response.json()
    print(dados)
    assert dados['ok'] is True
    
    # Invalido, sem ID
    delete_response = deletar(local_endpoint)
    dados = delete_response.json()
    print(dados)
    assert dados['ok'] is False
    
    # Invalido ID não existente
    id_invalido = 999
    print(f"ID Comanda: {id_invalido}")
    delete_response = deletar(local_endpoint,id_invalido)
    dados = delete_response.json()
    print(dados)
    assert dados['ok'] is False
    
    pass