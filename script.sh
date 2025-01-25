curl -X POST "http://localhost/restaurante/back/api/usuario.php" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer 6d174c9abd0f1747f99bbe168974c02bc1958f14b61af22c32a188bc15c09fe8" \
-d '{
    "email": "guilhermessmith2014@gmail.com",
    "login": "true"
}'

curl -X GET "http://localhost/restaurante/back/api/config.php" \
-H "Authorization: Bearer 6d174c9abd0f1747f99bbe168974c02bc1958f14b61af22c32a188bc15c09fe8"