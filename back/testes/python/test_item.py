import requests
import os
from config import ENDPOINT

os.system('clear')

def get_item(id_item = 0):
    return requests.get(ENDPOINT + f"/item.php?id={id_item}")

def test_get_item():
    get_item_response = get_item()
    assert get_item_response.status_code == 200
    
def test_get_nonexistent_item():
    get_item_response = get_item(5)
    assert get_item_response.status_code == 200