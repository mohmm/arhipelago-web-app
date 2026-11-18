<?php

/*
|--------------------------------------------------------------------------
| Mesaje de validare
|--------------------------------------------------------------------------
|
| Until this file existed, `__('validation.required')` returned "The nume field
| is required." to a Romanian visitor: there was no `lang/ro` directory at all,
| so every rejected form answered in English on a Romanian-first product.
|
| Phrased impersonally, and in the second person singular the one time a
| message speaks to the visitor, which is how the rest of the site addresses
| them. A count is never put in front of a noun: Romanian agrees the noun with
| the number („15 caractere", „255 de caractere"), one string cannot do both,
| so these say „numărul de caractere … :max" or use KB instead. `:attribute`
| is substituted with a field name from the `attributes` list at the bottom,
| so add a field there rather than writing the Romanian name into a rule.
|
*/

return [

    'accepted' => 'Câmpul :attribute trebuie acceptat.',
    'accepted_if' => 'Câmpul :attribute trebuie acceptat când :other este :value.',
    'active_url' => 'Câmpul :attribute nu este o adresă web validă.',
    'after' => 'Câmpul :attribute trebuie să fie o dată după :date.',
    'after_or_equal' => 'Câmpul :attribute trebuie să fie o dată după sau egală cu :date.',
    'alpha' => 'Câmpul :attribute poate conține doar litere.',
    'alpha_dash' => 'Câmpul :attribute poate conține doar litere, cifre, cratime și liniuțe de subliniere.',
    'alpha_num' => 'Câmpul :attribute poate conține doar litere și cifre.',
    'any_of' => 'Câmpul :attribute nu este valid.',
    'array' => 'Câmpul :attribute trebuie să fie o listă.',
    'ascii' => 'Câmpul :attribute poate conține doar caractere și simboluri alfanumerice pe un octet.',
    'before' => 'Câmpul :attribute trebuie să fie o dată înainte de :date.',
    'before_or_equal' => 'Câmpul :attribute trebuie să fie o dată înainte de sau egală cu :date.',
    'between' => [
        'array' => 'Câmpul :attribute trebuie să conțină un număr de elemente între :min și :max.',
        'file' => 'Fișierul :attribute trebuie să aibă între :min și :max KB.',
        'numeric' => 'Câmpul :attribute trebuie să fie între :min și :max.',
        'string' => 'Câmpul :attribute trebuie să aibă un număr de caractere între :min și :max.',
    ],
    'boolean' => 'Câmpul :attribute trebuie să fie adevărat sau fals.',
    'can' => 'Câmpul :attribute conține o valoare neautorizată.',
    'confirmed' => 'Confirmarea câmpului :attribute nu se potrivește.',
    'contains' => 'Câmpului :attribute îi lipsește o valoare necesară.',
    'current_password' => 'Parola este incorectă.',
    'date' => 'Câmpul :attribute nu este o dată validă.',
    'date_equals' => 'Câmpul :attribute trebuie să fie o dată egală cu :date.',
    'date_format' => 'Câmpul :attribute nu respectă formatul :format.',
    'decimal' => 'Numărul de zecimale din câmpul :attribute trebuie să fie :decimal.',
    'declined' => 'Câmpul :attribute trebuie refuzat.',
    'declined_if' => 'Câmpul :attribute trebuie refuzat când :other este :value.',
    'different' => 'Câmpurile :attribute și :other trebuie să fie diferite.',
    'digits' => 'Numărul de cifre din câmpul :attribute trebuie să fie :digits.',
    'digits_between' => 'Câmpul :attribute trebuie să aibă un număr de cifre între :min și :max.',
    'dimensions' => 'Imaginea :attribute are dimensiuni nevalide.',
    'distinct' => 'Câmpul :attribute conține o valoare duplicată.',
    'doesnt_contain' => 'Câmpul :attribute nu poate conține niciuna dintre valorile: :values.',
    'doesnt_end_with' => 'Câmpul :attribute nu se poate termina cu niciuna dintre valorile: :values.',
    'doesnt_start_with' => 'Câmpul :attribute nu poate începe cu niciuna dintre valorile: :values.',
    'email' => 'Câmpul :attribute trebuie să fie o adresă de email validă.',
    'ends_with' => 'Câmpul :attribute trebuie să se termine cu una dintre valorile: :values.',
    'enum' => 'Valoarea selectată pentru :attribute nu este validă.',
    'exists' => 'Valoarea selectată pentru :attribute nu este validă.',
    'extensions' => 'Fișierul :attribute trebuie să aibă una dintre extensiile: :values.',
    'file' => 'Câmpul :attribute trebuie să fie un fișier.',
    'filled' => 'Câmpul :attribute trebuie completat.',
    'gt' => [
        'array' => 'Numărul de elemente din câmpul :attribute trebuie să fie mai mare decât :value.',
        'file' => 'Fișierul :attribute trebuie să aibă mai mult de :value KB.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mare decât :value.',
        'string' => 'Numărul de caractere din câmpul :attribute trebuie să fie mai mare decât :value.',
    ],
    'gte' => [
        'array' => 'Numărul de elemente din câmpul :attribute trebuie să fie cel puțin :value.',
        'file' => 'Fișierul :attribute trebuie să aibă cel puțin :value KB.',
        'numeric' => 'Câmpul :attribute trebuie să fie cel puțin :value.',
        'string' => 'Numărul de caractere din câmpul :attribute trebuie să fie cel puțin :value.',
    ],
    'hex_color' => 'Câmpul :attribute trebuie să fie o culoare hexazecimală validă.',
    'image' => 'Câmpul :attribute trebuie să fie o imagine.',
    'in' => 'Valoarea selectată pentru :attribute nu este validă.',
    'in_array' => 'Câmpul :attribute nu există în :other.',
    'in_array_keys' => 'Câmpul :attribute trebuie să conțină cel puțin una dintre cheile: :values.',
    'integer' => 'Câmpul :attribute trebuie să fie un număr întreg.',
    'ip' => 'Câmpul :attribute trebuie să fie o adresă IP validă.',
    'ipv4' => 'Câmpul :attribute trebuie să fie o adresă IPv4 validă.',
    'ipv6' => 'Câmpul :attribute trebuie să fie o adresă IPv6 validă.',
    'json' => 'Câmpul :attribute trebuie să fie un text JSON valid.',
    'list' => 'Câmpul :attribute trebuie să fie o listă.',
    'lowercase' => 'Câmpul :attribute trebuie scris cu litere mici.',
    'lt' => [
        'array' => 'Numărul de elemente din câmpul :attribute trebuie să fie mai mic decât :value.',
        'file' => 'Fișierul :attribute trebuie să aibă mai puțin de :value KB.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mic decât :value.',
        'string' => 'Numărul de caractere din câmpul :attribute trebuie să fie mai mic decât :value.',
    ],
    'lte' => [
        'array' => 'Numărul de elemente din câmpul :attribute nu poate depăși :value.',
        'file' => 'Fișierul :attribute nu poate depăși :value KB.',
        'numeric' => 'Câmpul :attribute nu poate fi mai mare de :value.',
        'string' => 'Numărul de caractere din câmpul :attribute nu poate depăși :value.',
    ],
    'mac_address' => 'Câmpul :attribute trebuie să fie o adresă MAC validă.',
    'max' => [
        'array' => 'Numărul de elemente din câmpul :attribute nu poate depăși :max.',
        'file' => 'Fișierul :attribute nu poate depăși :max KB.',
        'numeric' => 'Câmpul :attribute nu poate fi mai mare de :max.',
        'string' => 'Numărul de caractere din câmpul :attribute nu poate depăși :max.',
    ],
    'max_digits' => 'Numărul de cifre din câmpul :attribute nu poate depăși :max.',
    'mimes' => 'Câmpul :attribute trebuie să fie un fișier de tip: :values.',
    'mimetypes' => 'Câmpul :attribute trebuie să fie un fișier de tip: :values.',
    'min' => [
        'array' => 'Numărul de elemente din câmpul :attribute trebuie să fie cel puțin :min.',
        'file' => 'Fișierul :attribute trebuie să aibă cel puțin :min KB.',
        'numeric' => 'Câmpul :attribute trebuie să fie cel puțin :min.',
        'string' => 'Numărul de caractere din câmpul :attribute trebuie să fie cel puțin :min.',
    ],
    'min_digits' => 'Numărul de cifre din câmpul :attribute trebuie să fie cel puțin :min.',
    'missing' => 'Câmpul :attribute trebuie să lipsească.',
    'missing_if' => 'Câmpul :attribute trebuie să lipsească atunci când :other este :value.',
    'missing_unless' => 'Câmpul :attribute trebuie să lipsească dacă :other nu este :value.',
    'missing_with' => 'Câmpul :attribute trebuie să lipsească atunci când :values este prezent.',
    'missing_with_all' => 'Câmpul :attribute trebuie să lipsească atunci când :values sunt prezente.',
    'multiple_of' => 'Câmpul :attribute trebuie să fie multiplu de :value.',
    'not_in' => 'Valoarea selectată pentru :attribute nu este validă.',
    'not_regex' => 'Formatul câmpului :attribute nu este valid.',
    'numeric' => 'Câmpul :attribute trebuie să fie un număr.',
    'password' => [
        'letters' => 'Parola trebuie să conțină cel puțin o literă.',
        'mixed' => 'Parola trebuie să conțină cel puțin o literă mare și o literă mică.',
        'numbers' => 'Parola trebuie să conțină cel puțin o cifră.',
        'symbols' => 'Parola trebuie să conțină cel puțin un simbol.',
        'uncompromised' => 'Această parolă a apărut într-o scurgere de date. Alege alta.',
    ],
    'present' => 'Câmpul :attribute trebuie să fie prezent.',
    'present_if' => 'Câmpul :attribute trebuie să fie prezent atunci când :other este :value.',
    'present_unless' => 'Câmpul :attribute trebuie să fie prezent dacă :other nu este :value.',
    'present_with' => 'Câmpul :attribute trebuie să fie prezent atunci când :values este prezent.',
    'present_with_all' => 'Câmpul :attribute trebuie să fie prezent atunci când :values sunt prezente.',
    'prohibited' => 'Câmpul :attribute nu este permis.',
    'prohibited_if' => 'Câmpul :attribute nu este permis atunci când :other este :value.',
    'prohibited_if_accepted' => 'Câmpul :attribute nu este permis atunci când :other este acceptat.',
    'prohibited_if_declined' => 'Câmpul :attribute nu este permis atunci când :other este refuzat.',
    'prohibited_unless' => 'Câmpul :attribute nu este permis dacă :other nu se află în :values.',
    'prohibits' => 'Câmpul :attribute nu permite prezența câmpului :other.',
    'regex' => 'Formatul câmpului :attribute nu este valid.',
    'required' => 'Câmpul :attribute este obligatoriu.',
    'required_array_keys' => 'Câmpul :attribute trebuie să conțină intrări pentru: :values.',
    'required_if' => 'Câmpul :attribute este obligatoriu atunci când :other este :value.',
    'required_if_accepted' => 'Câmpul :attribute este obligatoriu atunci când :other este acceptat.',
    'required_if_declined' => 'Câmpul :attribute este obligatoriu atunci când :other este refuzat.',
    'required_unless' => 'Câmpul :attribute este obligatoriu dacă :other nu se află în :values.',
    'required_with' => 'Câmpul :attribute este obligatoriu atunci când :values este prezent.',
    'required_with_all' => 'Câmpul :attribute este obligatoriu atunci când :values sunt prezente.',
    'required_without' => 'Câmpul :attribute este obligatoriu atunci când :values lipsește.',
    'required_without_all' => 'Câmpul :attribute este obligatoriu atunci când niciunul dintre :values nu este prezent.',
    'same' => 'Câmpurile :attribute și :other trebuie să coincidă.',
    'size' => [
        'array' => 'Numărul de elemente din câmpul :attribute trebuie să fie :size.',
        'file' => 'Fișierul :attribute trebuie să aibă :size KB.',
        'numeric' => 'Câmpul :attribute trebuie să fie :size.',
        'string' => 'Numărul de caractere din câmpul :attribute trebuie să fie :size.',
    ],
    'starts_with' => 'Câmpul :attribute trebuie să înceapă cu una dintre valorile: :values.',
    'string' => 'Câmpul :attribute trebuie să fie un text.',
    'timezone' => 'Câmpul :attribute trebuie să fie un fus orar valid.',
    'ulid' => 'Câmpul :attribute trebuie să fie un ULID valid.',
    'unique' => 'Această valoare pentru :attribute este deja folosită.',
    'uploaded' => 'Încărcarea fișierului :attribute a eșuat.',
    'uppercase' => 'Câmpul :attribute trebuie scris cu litere mari.',
    'url' => 'Câmpul :attribute trebuie să fie o adresă web validă.',
    'uuid' => 'Câmpul :attribute trebuie să fie un UUID valid.',

    /*
    |--------------------------------------------------------------------------
    | Mesaje personalizate
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mesaj-personalizat',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Numele câmpurilor
    |--------------------------------------------------------------------------
    |
    | Substituted for `:attribute`. Without an entry a field appears under its
    | database column name — "Câmpul point_of_interest_id este obligatoriu."
    |
    */

    'attributes' => [
        'name' => 'nume',
        'first_name' => 'prenume',
        'last_name' => 'nume de familie',
        'email' => 'email',
        'phone' => 'telefon',
        'message' => 'mesaj',
        'password' => 'parolă',
        'password_confirmation' => 'confirmarea parolei',
        'current_password' => 'parola actuală',
        'description' => 'descriere',
        'role' => 'rol',
        'company' => 'companie',
        'terms' => 'termenii și condițiile',
        'privacy' => 'politica de confidențialitate',
    ],

];
