import random
ENDPOINT = 'http://localhost/restaurante/back/api'

def random_float(low = 5, high = 50):
    return round(random.uniform(low,high),2)