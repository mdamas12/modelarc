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

    'callmebot' => [
        'enabled' => env('CALLMEBOT_ENABLED', false),
        'phone' => env('CALLMEBOT_PHONE', ''),
        'apikey' => env('CALLMEBOT_APIKEY', ''),
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

    /*
    |--------------------------------------------------------------------------
    | Meta Pixel + Conversions API
    |--------------------------------------------------------------------------
    |
    | Pixel ID is public. Access token is server-only — never expose to SPA,
    | site_settings, admin UI, docs, or tests as a real secret value.
    |
    */
    'meta' => [
        'pixel_id' => env('META_PIXEL_ID', ''),
        'access_token' => env('META_CONVERSIONS_API_TOKEN', ''),
        'pixel_enabled' => filter_var(env('META_PIXEL_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'conversions_enabled' => filter_var(env('META_CONVERSIONS_API_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'test_event_code' => env('META_TEST_EVENT_CODE', ''),
        'graph_api_version' => env('META_GRAPH_API_VERSION', 'v21.0'),
    ],

];
