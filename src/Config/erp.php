<?php

return [
    /**
     * The URL of your Spring Boot ERP system.
     * Example: 'http://localhost:8080' or 'https://api.your-erp.com'
     */
    'url' => env('ERP_BASE_URL', 'http://localhost:8080'),

    /**
     * Authentication token used by Bagisto to authenticate with the ERP,
     * and also expected by Bagisto from the ERP when receiving webhooks.
     */
    'api_token' => env('ERP_API_TOKEN', 'your-secure-token-here'),
];
