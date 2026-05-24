<?php

return [
    [
        'key'   => 'erp',
        'name'  => 'erp::app.admin.system.erp-connector',
        'info'  => 'erp::app.admin.system.info',
        'sort'  => 10,
    ],
    [
        'key'   => 'erp.settings',
        'name'  => 'erp::app.admin.system.settings',
        'info'  => 'erp::app.admin.system.info',
        'sort'  => 1,
    ],
    [
        'key'    => 'erp.settings.general',
        'name'   => 'erp::app.admin.system.general',
        'info'   => 'erp::app.admin.system.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'backend_url',
                'title'         => 'erp::app.admin.system.backend-url',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'erp_token',
                'title'         => 'erp::app.admin.system.erp-token',
                'type'          => 'password',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'connection_test_button',
                'title'         => '',
                'type'          => 'blade',
                'path'          => 'erp::admin.connection-test-field',
            ]
        ]
    ]
];
