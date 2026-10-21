<?php

return [
    'domain' => env('ADMIN_DOMAIN', 'succezzdriversagency.com'),
    'system' => [
        'email' => env('SYSTEM_ADMIN_EMAIL', 'succezzdriversagency@gmail.com'),
        'password' => env('SYSTEM_ADMIN_PASSWORD'),
    ],
    'cms' => [
        'email' => env('CMS_ADMIN_EMAIL', 'Succezzstephen14@gmail.com'),
        'password' => env('CMS_ADMIN_PASSWORD'),
    ],
];
