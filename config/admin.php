<?php

return [
    'system' => [
        'email' => env('SYSTEM_ADMIN_EMAIL', 'succezzdriversagency@gmail.com'),
        'password' => env('SYSTEM_ADMIN_PASSWORD'),
    ],
    'cms' => [
        'email' => env('CMS_ADMIN_EMAIL', 'site.admin@dashboard.local'),
        'password' => env('CMS_ADMIN_PASSWORD', 'SiteAdm@2026!'),
    ],
];
