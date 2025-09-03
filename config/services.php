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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'kling' => [
        'api_url' => env('KLING_API_URL', 'https://api-singapore.klingai.com'),
        'access_key' => env('KLING_ACCESS_KEY'),
        'secret_key' => env('KLING_SECRET_KEY'),
    ],

    'servientrega' => [
        'api_url' => env('SERVIENTREGA_API_URL', 'https://wssismilenio.servientrega.com/wsrastreoenvios/wsrastreoenvios.asmx/ConsultarGuia'),
        'username' => env('USERNAME'),
        'password' => env('PASSWORD')
    ],

    'chatai' => [
        'api_key' => env('CHAT_AI_API_KEY'),
        'model' => env('CHAT_AI_MODEL', 'gemini-2.5-flash'),
        'api_url' => env('CHAT_AI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta')
    ]
];
