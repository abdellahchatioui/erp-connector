<?php
return [
    'token_url'     => env('KEYCLOAK_TOKEN_URL', 'http://localhost:8080/realms/demo/protocol/openid-connect/token'),
    'client_id'     => env('KEYCLOAK_CLIENT_ID', 'erp'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
];
