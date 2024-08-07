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
        'view_path' =>[
            'webpress' => base_path() . '/modules/framework/src/webpress/component/src/resources/views/components',
            'app' => resource_path('views/components'),
        ],
        'class_default' => base_path() . '/modules/framework/src/webpress/component/src/app/Components/Default.php',
    ],

    'livewire' => [
        'class_namespace' => [
            'webpress' => 'Webpress\\Livewire\\Livewire',
            'app' => 'App\\Livewire',
        ],
        'class_path' => [
            'webpress' => base_path() . '/modules/framework/src/webpress/livewire/src/app/Http/Livewire',
            'app' => app_path('Livewire'),
        ],
        'view_path' =>[
            'webpress' => __DIR__ . '../modules/livewire/src/resources/views',
            'app' => resource_path('views/livewire'),
        ],
        'class_default' => base_path() . '/modules/livewire/src/app/Livewire/Default.php',
    ],
];
