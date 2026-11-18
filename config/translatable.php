<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content languages
    |--------------------------------------------------------------------------
    |
    | Translated content is stored one column per language ("name_ro",
    | "name_en", "name_ar"). Romanian is always written, so it is what a
    | missing translation falls back to.
    |
    */

    'languages' => [
        'ro' => ['name' => 'Romanian', 'native' => 'Română', 'dir' => 'ltr'],
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl'],
    ],

    'fallback' => 'ro',

];
