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

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'anon_key' => env('SUPABASE_ANON_KEY'),
        'service_key' => env('SUPABASE_SERVICE_KEY'),
    ],

    'twocheckout' => [
        'account_number' => env('TWOCHECKOUT_ACCOUNT_NUMBER'),
        'secret_key' => env('TWOCHECKOUT_SECRET_KEY'),
        'publishable_key' => env('TWOCHECKOUT_PUBLISHABLE_KEY'),
        'product_code' => env('TWOCHECKOUT_PRODUCT_CODE', 'QUOTE_PAYMENT'),
        'sandbox' => env('TWOCHECKOUT_SANDBOX', true),
        'currency' => env('TWOCHECKOUT_CURRENCY', 'USD'),
        'secret_word' => env('TWOCHECKOUT_SECRET_WORD'),
        'api_base' => env('TWOCHECKOUT_API_BASE', 'https://api.2checkout.com/rest/6.0'),
    'verify_ssl' => env('TWOCHECKOUT_VERIFY_SSL', true),
    'ca_bundle' => env('TWOCHECKOUT_CA_BUNDLE'), // absolute path to cacert.pem if needed
    'auth_mode' => env('TWOCHECKOUT_AUTH_MODE', 'basic'), // basic | hmac
    ],

    'embroidery_api' => [
        'url' => env('EMBROIDERY_API_URL', 'http://162.0.236.226'),
        'key' => env('EMBROIDERY_API_KEY', '9097332919794dea83dd2de22191ec913a1b8f44'),
        'timeout' => env('EMBROIDERY_API_TIMEOUT', 300),
    ],
];
