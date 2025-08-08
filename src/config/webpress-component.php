<?php

return [
    'component' => [
        'class_namespace' => [
            'webpress' => 'Webpress\\Component\\Components',
            'app' => 'App\\Components',
        ],
        'class_path' => [
            'webpress' => base_path() . '/modules/framework/src/webpress/component/src/app/Components',
            'app' => app_path('Components'),
        ],
        'view_path' => [
            'webpress' => base_path() . '/modules/framework/src/webpress/component/src/resources/views/components',
            'app' => resource_path('views/components'),
        ],
        'view_prefix' => [
            'webpress' => 'webpress.component::components.',
            'app' => 'components.',
        ],
        'subfix_name' => [
            'webpress' => '',
            'app' => 'Component',
        ]
    ],

    'livewire' => [
        'class_namespace' => [
            'webpress' => 'Webpress\\Livewire\\Livewire',
            'app' => 'App\\Livewire',
        ],
        'class_path' => [
            'webpress' => base_path() . '/modules/livewire/src/app/Livewire',
            'app' => app_path('Livewire'),
        ],
        'view_path' => [
            'webpress' => base_path('modules/livewire/src/resources/views'),
            'app' => resource_path('views/livewire'),
        ],
        'view_prefix' => [
            'webpress' => 'webpress.livewire::',
            'app' => 'livewire.',
        ],
        'subfix_name' => [
            'webpress' => '',
            'app' => 'Livewire',
        ]
    ],

    // Cấu hình mặc định cho các type mới
    'default_settings' => [
        'component' => [
            'class_namespace' => 'App\\Components',
            'class_path' => 'app/Components',
            'view_path' => 'resources/views/components',
            'view_prefix' => 'components.',
            'subfix_name' => 'Component',
        ],
        'livewire' => [
            'class_namespace' => 'App\\Livewire',
            'class_path' => 'app/Livewire',
            'view_path' => 'resources/views/livewire',
            'view_prefix' => 'livewire.',
            'subfix_name' => 'Livewire',
        ],
    ],
];
