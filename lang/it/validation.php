<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute deve essere accettato.',
    'active_url' => ':attribute non è una URL valida.',
    'after' => ':attribute deve essere una data successiva a :date.',
    'after_or_equal' => ':attribute deve essere una data successiva od uguale a :date.',
    'alpha' => ':attribute può contenere solo lettere.',
    'alpha_dash' => ':attribute può contenere solo lettere, numeri, trattino ed underscore.',
    'alpha_num' => ':attribute può contenere solo lettere e numeri.',
    'alpha_space' => ':attribute può contenere solo lettere, numeri, underscore e spazi.',
    'array' => ':attribute deve essere un array.',
    'before' => ':attribute deve essere una data precedente a :date.',
    'before_or_equal' => ':attribute deve essere una data precedente od uguale a :date.',
    'between' => [
        'numeric' => ':attribute deve essere compreso tra :min e :max.',
        'file' => ':attribute deve essere compreso tra :min e :max kilobyte.',
        'string' => ':attribute deve essere compreso tra :min e :max caratteri.',
        'array' => ':attribute deve essere compreso tra :min e :max elementi.',
    ],
    'boolean' => ':attribute può essere solo vero o falso.',
    'confirmed' => ':attribute di conferma non corrispondente.',
    'date' => ':attribute non è una data valida.',
    'date_equals' => ':attribute deve essere una data uguale a :date.',
    'date_format' => ':attribute non corrisponde al formato :format.',
    'different' => ':attribute e :other devono essere diversi.',
    'digits' => ':attribute devono essere cifre :digits.',
    'digits_between' => ':attribute devono essere cifre comprese tra :min e :max.',
    'dimensions' => 'l\'immagine :attribute ha dimensioni non valide.',
    'distinct' => ':attribute campo con valore duplicato.',
    'email' => ':attribute deve essere un indirizzo e-mail valido.',
    'ends_with' => ':attribute deve terminare con: :values',
    'exists' => ':attribute non valido.',
    'file' => ':attribute deve essere un file.',
    'filled' => ':attribute deve contenere un valore.',
    'gt' => [
        'numeric' => ':attribute deve essere maggiore di :value.',
        'file' => ':attribute deve essere più grande di :value kilobyte.',
        'string' => ':attribute deve essere più lungo di :value caratteri.',
        'array' => ':attribute deve avere più di :value elementi.',
    ],
    'gte' => [
        'numeric' => ':attribute deve essere maggiore o uguale a :value.',
        'file' => ':attribute deve essere uguale o più grande di :value kilobyte.',
        'string' => ':attribute deve essere uguale o più lungo di :value caratteri.',
        'array' => ':attribute deve avere almeno :value elementi.',
    ],
    'image' => ':attribute deve essere una immagine.',
    'in' => ':attribute non è valido.',
    'in_array' => ':attribute non esiste in :other.',
    'integer' => ':attribute deve essere un intero.',
    'ip' => ':attribute deve essere un indirizzo IP valido.',
    'ip_or_hostname' => ':attribute deve essere un indirizzo IP od un nome valido.',
    'ipv4' => ':attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => ':attribute deve essere un indirizzo IPv6 valido.',
    'is_regex' => ':attribute non è un\'espressione regolare valida.',
    'json' => ':attribute deve essere una stringa JSON valida.',
    'keys_in' => ':attribute contiene una chiave non valida: :extra. Chiavi valide: :values',
    'lt' => [
        'numeric' => ':attribute deve essere minori di :value.',
        'file' => ':attribute deve essere più piccolo di :value kilobyte.',
        'string' => ':attribute deve essere più corto di :value caratteri.',
        'array' => ':attribute deve avere meno di :value elementi.',
    ],
    'lte' => [
        'numeric' => ':attribute deve essere minore o uguale a :value.',
        'file' => ':attribute deve essere uguale o più piccolo di :value kilobyte.',
        'string' => ':attribute deve essere uguale o più corta di :value caratteri.',
        'array' => ':attribute deve avere meno di :value elementi.',
    ],
    'max' => [
        'numeric' => ':attribute non può essere maggiore di :max.',
        'file' => 'attribute non può essere più grande di :max kilobyte.',
        'string' => ':attribute non può essere più lunga di :max caratteri.',
        'array' => ':attribute non può avere più di :max elementi.',
    ],
    'mimes' => ':attribute deve essere un tipo di file: :values.',
    'mimetypes' => ':attribute deve essere un tipo di file: :values.',
    'min' => [
        'numeric' => ':attribute deve essere almeno :min.',
        'file' => ':attribute deve essere almeno :min kilobyte.',
        'string' => ':attribute deve essere lungo almeno :min caratteri.',
        'array' => ':attribute deve avere almeno :min elementi.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => ':attribute selezione non valida.',
    'not_regex' => ':attribute formato non valido.',
    'numeric' => ':attribute deve essere un numero.',
    'password' => 'La password non è corretta.',
    'present' => ':attribute deve avere un valore.',
    'regex' => ':attribute formato non valido.',
    'required' => ':attribute è un campo obbligatorio.',
    'required_if' => ':attribute è un campo obbligatorio quando :other è :value.',
    'required_unless' => ':attribute è un campo obbligatorio tranne quando :other è impostato su :values.',
    'required_with' => ':attribute è un campo obbligatorio quando :values è presente.',
    'required_with_all' => ':attribute è un campo obbligatorio quando :values sono presenti.',
    'required_without' => ':attribute è un campo obbligatorio quando :values non è presente.',
    'required_without_all' => ':attribute è un campo obbligatorio quando :values non sono presenti.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'La presenza del campo :attribute non è ammessa se :other non è in :values.',
    'prohibits' => 'La presenza del campo :attribute non è ammessa se è presente anche :other.',
    'same' => ':attribute e :other devono essere uguali.',
    'size' => [
        'numeric' => ':attribute deve essere :size.',
        'file' => ':attribute deve essere di :size kilobyte.',
        'string' => ':attribute deve essere di :size caratteri.',
        'array' => ':attribute deve contenere :size elementi.',
    ],
    'starts_with' => ':attribute deve iniziare con: :values',
    'string' => ':attribute deve essere una stringa.',
    'timezone' => ':attribute deve essere un fuso orario valido.',
    'unique' => ':attribute è un elemento già presente.',
    'uploaded' => ':attribute caricamento fallito.',
    'url' => ':attribute formato non valido.',
    'uuid' => ':attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
