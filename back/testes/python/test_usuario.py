import requests
import os
from config import *

local_endpoint = ENDPOINT + '/usuario.php'

def criar_usuario(payload):
    return requests.post(local_endpoint, json=payload)

def atualizar_usuario():
    return requests.get(local_endpoint+'')

def deletar_usuario(id):
    return requests.delete(local_endpoint+f"?id={id}")

def new_payload():
    return {
        'nome': 'Guilherme',
        'email': 'guilhermessmith2024@gmail.com',
        'senha': 'Senha123@'
    }

os.system('clear')

def test_criar_usuario():
    payload = new_payload()
    post_response = criar_usuario(payload)
    print(post_response.json())
    assert post_response.status_code == 200
    id_usuario = post_response.json()['id']
    delete_response = deletar_usuario(id_usuario)
    assert delete_response.status_code == 200
    pass

def test_deletar_usuario():
    payload = new_payload()
    post_response = criar_usuario(payload)
    print(post_response.json())
    assert post_response.status_code == 200
    id_usuario = post_response.json()['id']
    delete_response = deletar_usuario(id_usuario)
    assert delete_response.status_code == 200
    pass