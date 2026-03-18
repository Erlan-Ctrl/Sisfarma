<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // GTIN/EAN lookup (Datakick via GTINSearch). Useful to prefill the catalog when a barcode is not yet in DB.
    'gtinsearch' => [
        'base_url' => env('GTINSEARCH_BASE_URL', 'https://www.gtinsearch.org/api'),
        // Optional. If you create an account, you can authenticate requests.
        'token' => env('GTINSEARCH_TOKEN'),
        'timeout' => (int) env('GTINSEARCH_TIMEOUT', 6),
        'cache_days' => (int) env('GTINSEARCH_CACHE_DAYS', 60),
    ],

];
