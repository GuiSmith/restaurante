import os
from config import *

local_endpoint = '/itemcomanda.php'

# Criando comanda
print('Criando comanda...')
post_response = criar('/comanda.php')
assert post_response.json()['ok'] is True
comanda_id = post_response.json()['id']

# Criando item
print('Criando item...')
post_response = criar('/item.php',{
    'descricao': 'test_post',
    'valor': random_float(),
    'tipo': 'PRATO'
    }
)
assert post_response.json()['ok'] is True
item_id = post_response.json()['id']

itemcomanda_id = 0

os.system('clear')

# POST / CREATE
def test_post_itemcomanda():
    print()
    
    global comanda_id
    global item_id
    global itemcomanda_id
    
    # Valido
    data = {
        'comanda_id': comanda_id,
        'item_id': item_id,
        'quantidade': random_int()
    }
    post_response = criar(local_endpoint,data)
    dados = post_response.json()
    print(dados)
    assert dados['ok'] is True
    itemcomanda_id = dados['id']
    
    import requests

    # Dicionário com ID do item faltando
    dados_faltando_id_item = {
        # "id_item" está faltando
        "id_comanda": 1,
        "quantidade": 2
    }

    # Dicionário com ID da comanda faltando
    dados_faltando_id_comanda = {
        "id_item": 1,
        # "id_comanda" está faltando
        "quantidade": 2
    }

    # Dicionário com quantidade faltando
    dados_faltando_quantidade = {
        "id_item": 1,
        "id_comanda": 1,
        # "quantidade" está faltando
    }

    # Dicionário com quantidade zero
    quantidade_zero = {
        "id_item": 1,
        "id_comanda": 1,
        "quantidade": 0  # Quantidade não pode ser zero
    }

    # Dicionário com quantidade negativa
    quantidade_negativa = {
        "id_item": 1,
        "id_comanda": 1,
        "quantidade": -1  # Quantidade não pode ser negativa
    }

    # Dicionário com todos os dados inválidos
    todos_invalidos = {
        # "id_item" está faltando
        # "id_comanda" está faltando
        "quantidade": 0  # Quantidade não pode ser zero
    }

    # Função para testar os dados
    def testar_dados(dados):
        post_response = requests.post(local_endpoint, json=dados)
        dados_resposta = post_response.json()
        print(dados_resposta)
        assert dados_resposta['ok'] is False

    # Testando os dicionários
    testar_dados(dados_faltando_id_item)
    testar_dados(dados_faltando_id_comanda)
    testar_dados(dados_faltando_quantidade)
    testar_dados(quantidade_zero)
    testar_dados(quantidade_negativa)
    testar_dados(todos_invalidos)