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
    print(post_response.json())
    comanda_id = post_response.json()['id']
    assert post_response.status_code == 201
    pass
    
# GET / READ
def test_get_comanda():
    print()
    
    # Valido por ID
    print(f"ID Comanda: {comanda_id}")
    get_response = selecionar(local_endpoint,comanda_id)
    print(get_response.json())
    assert get_response.status_code == 200
    
    # Invalido por ID
    id_invalido = 999
    print(f"ID Comanda: {id_invalido}")
    get_response = selecionar(local_endpoint,id_invalido)
    print(get_response.json())
    assert get_response.status_code == 204
    
    # Invalido, sem ID
    print(f"ID Comanda: ")
    get_response = selecionar(local_endpoint)
    print(get_response.json())
    assert get_response.status_code == 400
    
    pass

def test_update_comanda():
    print()
        
    # Valido
    print(f"ID Comanda: {comanda_id}")
    put_response = atualizar(local_endpoint,{'id': comanda_id})
    print(put_response.json())
    assert put_response.status_code == 200
    
    # Invalido, sem passar ID
    print(f"ID Comanda: nenhum")
    put_response = atualizar(local_endpoint)
    print(put_response.json())
    assert put_response.status_code == 400
    
    # Invalido, id não encontrado
    id_invalido = 999
    print(f"ID Comanda: {id_invalido}")
    put_response = atualizar(local_endpoint,{'id': id_invalido})
    print(put_response.json())
    assert put_response.status_code == 404
    
def test_delete_comanda():
    print()
    
    # Valido
    delete_response = deletar(local_endpoint,comanda_id)
    print(delete_response.json())
    assert delete_response.status_code == 200
    
    # Invalido, sem ID
    delete_response = deletar(local_endpoint)
    print(delete_response.json())
    assert delete_response.status_code == 400
    
    # Invalido ID não existente
    id_invalido = 999
    delete_response = deletar(local_endpoint,id_invalido)
    print(delete_response.json())
    assert delete_response.status_code == 404
    
    pass