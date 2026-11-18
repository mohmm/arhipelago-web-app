<?php

/*
|--------------------------------------------------------------------------
| Mesaje de autentificare
|--------------------------------------------------------------------------
|
| Without this file the sign-in form answered a Romanian visitor in English,
| because `auth.failed` fell back to the framework's own copy. Second person
| singular, like the rest of the site. The wait is printed in seconds with the
| unit abbreviated, so the sentence is correct for any number the limiter
| returns — „30 de secunde" and „5 secunde" cannot share one string.
|
*/

return [

    'failed' => 'Adresa de e-mail sau parola nu sunt corecte.',
    'password' => 'Parola nu este corectă.',
    'throttle' => 'Prea multe încercări de autentificare. Încearcă din nou peste :seconds s.',

];
