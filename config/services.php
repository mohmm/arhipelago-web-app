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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | The map tiles drawn under every map, in the admin panel and in the app.
    | OpenStreetMap's own servers suit development; production traffic needs
    | a tile provider (set MAP_TILES_URL and MAP_TILES_ATTRIBUTION).
    */
    'map_tiles' => [
        'url' => env('MAP_TILES_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('MAP_TILES_ATTRIBUTION', '© OpenStreetMap contributors'),
    ],

    /*
    | OSRM, the router behind every experience: distances, durations and the
    | line drawn on the map are measured on real roads. One instance per
    | travel mode. The foot container answers on the `driving` path too.
    */
    'osrm' => [
        'profiles' => [
            'car' => env('OSRM_URL_CAR', 'http://localhost:5001'),
            'foot' => env('OSRM_URL_FOOT', 'http://localhost:5002'),
        ],
        'timeout' => (int) env('OSRM_TIMEOUT', 20),
        'max_table_size' => (int) env('OSRM_MAX_TABLE_SIZE', 1000),
    ],

];
