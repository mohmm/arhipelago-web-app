<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The app's accounts
    |--------------------------------------------------------------------------
    |
    | Null: anyone can create an account in the app. A number: the app has that
    | many accounts and no more, by design. They are user1@<domain> to
    | user<limit>@<domain>, made with `php artisan accounts:create`, and nobody
    | can sign up in the app.
    |
    | Arhipelago gives its people 50 accounts: user1@arhipelago.ro to
    | user50@arhipelago.ro.
    |
    */

    'limit' => 50,

    'domain' => 'arhipelago.ro',

];
