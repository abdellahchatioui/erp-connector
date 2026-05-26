<?php
return [
    'token_url'     => env('KEYCLOAK_TOKEN_URL', 'http://localhost:8080/realms/demo/protocol/openid-connect/token'),
    'client_id'     => env('KEYCLOAK_CLIENT_ID', 'erp'),
    'username'  => env('KEYCLOAK_USERNAME', 'abdo'),
    'password'  => env('KEYCLOAK_PASSWORD', 'abdo'),
];
