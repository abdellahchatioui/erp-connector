<?php

return [
    'admin' => [
        'system' => [
            'erp-connector' => 'ERP Connector',
            'settings' => 'Settings',
            'general' => 'General',
            'backend-url' => 'ERP Backend URL',
            'erp-token' => 'ERP Token',
            'info' => 'Enter your ERP backend connection details.',
            'info_with_button' => 'Enter your ERP backend connection details. <button type="button" class="btn btn-primary" onclick="window.testErpConnection()" style="margin-top: 10px; display: block;">Test Connection</button>',
        ],

        'connection' => [
            'title' => 'Test ERP Connection',
            'test' => 'Test Connection',
            'success' => 'Connection established successfully!',
            'failed' => 'Connection failed: :message',
            'checking' => 'Checking connection...',
        ],

        'menu' => [
            'title' => 'ERP Integration',
        ]
    ]
];
