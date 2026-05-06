<?php

return [
    'name' => 'Yuexia',
    'tagline' => 'Moonlit Personal Space',
    'base_url' => '/',
    'view_path' => dirname(__DIR__) . '/app/Views/',
    'layout' => 'layouts/main',
    'upload_path' => dirname(__DIR__) . '/public/uploads/',
    'upload_url' => '/uploads/',
    'debug' => true,
    'timezone' => 'Asia/Shanghai',
    'mail' => [
        'enabled' => false,
        'to' => '',
        'from' => '',
        'from_name' => 'Yuexia',
        'subject_prefix' => '[Yuexia] ',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'yuexia',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ]
];
