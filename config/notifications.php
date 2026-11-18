<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redirect every outgoing email
    |--------------------------------------------------------------------------
    |
    | Unset in production. Set it on a copy of the database and every email
    | goes to this one mailbox instead of a real person.
    |
    */

    'always_to' => env('MAIL_ALWAYS_TO'),

];
