<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paytm-wallet' => [
        'env' => 'local', // values : (local | production)
        'merchant_id' => 'Androi78288874845632',
        'merchant_key' => '0Q8Z4v@a6POCVsd7',
        'merchant_website' => 'APPSTAGING',
        'channel' => 'WAP',
        'industry_type' => 'Retail',
    ],

    // Androi78288874845632 | 0Q8Z4v@a6POCVsd7
    // SxxGda21141640109716 | T5k09vacfMNU&50g

];
