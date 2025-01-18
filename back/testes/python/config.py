import requests
import os
import random
import json

api_url = 'http://localhost/restaurante/back/api'

def random_float(low = 5, high = 50):
    return round(random.uniform(low,high),2)

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
  url = api_url+endpoint+f"?id={id}" if id!=None else ""
  if debug:
    print(url)
  return requests.get(url)

def deletar(endpoint, id=None):
  if id is None:
    return requests.delete(api_url+endpoint)
  else:
    return requests.delete(api_url+endpoint+f"?id={id}")