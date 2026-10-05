<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'pakasir' => [
        'base_url' => env('PAKASIR_BASE_URL', 'https://app.pakasir.com'),
        'project' => env('PAKASIR_PROJECT', 'aromapalace-id'),
        'api_key' => env('PAKASIR_API_KEY', 'demo_pakasir_key'),
        'webhook_secret' => env('PAKASIR_WEBHOOK_SECRET', ''),
    ],

    'kiriminaja' => [
        'mode' => env('KIRIMINAJA_MODE', 'staging'),
        'base_url' => env('KIRIMINAJA_BASE_URL', 'https://tdev.kiriminaja.com'),
        'api_key' => env('KIRIMINAJA_API_KEY', 'demo_kiriminaja_key'),
        'order_prefix' => env('KIRIMINAJA_ORDER_PREFIX', 'AP-'),
        'sender_name' => env('KIRIMINAJA_SENDER_NAME', 'Aroma Palace Haute Parfumerie'),
        'sender_phone' => env('KIRIMINAJA_SENDER_PHONE', '081100001111'),
        'sender_city_id' => env('KIRIMINAJA_SENDER_CITY_ID', 151), // Jakarta Pusat
        'cache_store' => env('KIRIMINAJA_CACHE_STORE', 'file'),
        'cache_prefix' => env('KIRIMINAJA_CACHE_PREFIX', 'kiriminaja:'),
        'allow_simulation_fallback' => env('KIRIMINAJA_ALLOW_SIMULATION_FALLBACK', true),
    ],

    'carto' => [
        'api_key' => env('CARTO_API_KEY', ''),
    ],

];
