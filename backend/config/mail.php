<?php

return [
    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
        'log' => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
    ],

    // SMTP here is the bootstrap value only. Once Development Roadmap
    // Milestone 1 ships the Settings module, SMTP becomes editable from
    // the CMS (Database Design, settings table) and a runtime mailer
    // config override reads from there instead of purely from env.
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@pnknowledgecampus.edu'),
        'name' => env('MAIL_FROM_NAME', 'PN Knowledge Campus'),
    ],
];
