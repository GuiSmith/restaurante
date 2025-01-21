import requests
import os
import random
import json

api_url = 'http://localhost/restaurante/back/api'

def random_float(low = 5, high = 50):
    return round(random.uniform(low,high),2)

def random_int(low = 1, high = 10):
    return random.randint(low,high)

def criar(endpoint, payload=None):
  if payload is None:
    return requests.post(api_url+endpoint)
  else:
    return requests.post(api_url+endpoint, json=payload)

def atualizar(endpoint, payload=None):
  if payload is None:
    return requests.put(api_url+endpoint)
  else:
    return requests.put(api_url+endpoint, json=payload)

def selecionar(endpoint, id=None, debug = False):
  if id is None:
    return requests.get(api_url+endpoint)
  else:
    return requests.get(api_url+endpoint+f"?id={id}")

def deletar(endpoint, id=None):
  print(id)
  if id is None:
    return requests.delete(api_url+endpoint)
  else:
    return requests.delete(api_url+endpoint+f"?id={id}")