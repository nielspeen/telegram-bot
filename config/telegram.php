<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your Telegram bot settings
    |
    */

    'username' => env('TELEGRAM_USERNAME'),
    'token' => env('TELEGRAM_TOKEN'),

    'api_url' => 'https://api.telegram.org/bot',
    'webhook_url' => env('APP_WEBHOOK_URL'),
];
