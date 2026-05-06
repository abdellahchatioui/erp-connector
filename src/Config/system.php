<?php

return [
    [
        'key'    => 'erp',
        'name'   => 'erp::app.admin.system.erp-connector',
        'sort'   => 10,
    ], [
        'key'    => 'erp.settings',
        'name'   => 'erp::app.admin.system.settings',
        'sort'   => 1,
    ], [
        'key'    => 'erp.settings.general',
        'name'   => 'erp::app.admin.system.general',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'backend_url',
                'title'         => 'erp::app.admin.system.backend-url',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'erp_token',
                'title'         => 'erp::app.admin.system.erp-token',
                'type'          => 'password',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => false,
                'info'          => 'erp::app.admin.system.info_with_button',
            ]
        ]
    ]
];
