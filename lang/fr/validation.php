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

    'accepted' => ':attribute doit être accepté.',
    'active_url' => ":attribute n'est pas une URL valide.",
    'after' => ':attribute doit être une date après :date.',
    'after_or_equal' => ':attribute doit être une date après ou égale à :date.',
    'alpha' => ':attribute ne peut contenir que des lettres.',
    'alpha_dash' => ':attribute ne peut contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num' => ':attribute ne peut contenir que des lettres et des chiffres.',
    'alpha_space' => ':attribute ne peut contenir que des lettres, des chiffres, des underscores et des espaces.',
    'array' => ':attribute doit être un tableau.',
    'before' => ':attribute doit être une date avant :date.',
    'before_or_equal' => ':attribute doit être une date antérieure ou égale à :date.',
    'between' => [
        'numeric' => ':attribute doit être entre :min et :max.',
        'file' => ':attribute doit être entre :min et :max kilobytes.',
        'string' => ':attribute doit être entre :min et :max caractères.',
        'array' => ':attribute doit être entre :min et :max objets.',
    ],
    'boolean' => ':attribute le champ doit être vrai ou faux.',
    'confirmed' => ':attribute la confirmation ne correspond pas.',
    'date' => ":attribute n'est pas une date valide.",
    'date_equals' => ':attribute doit être une date égale à :date.',
    'date_format' => ':attribute ne correspond pas au format :format.',
    'different' => ':attribute et :other doit être différent.',
    'digits' => ':attribute doit être :digits chiffres.',
    'digits_between' => ':attribute doit être entre :min et :max chiffres.',
    'dimensions' => ":attribute a des dimensions d'image non valides.",
    'distinct' => ':attribute le champ a une valeur en double.',
    'email' => ':attribute doit être une adresse e-mail valide.',
    'ends_with' => ':attribute doit se terminer par l’un des éléments suivants: :values',
    'exists' => 'La sélection :attribute est invalide.',
    'file' => ':attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'numeric' => ':attribute doit être supérieur à :value.',
        'file' => ':attribute doit être supérieur à :value kilobytes.',
        'string' => ':attribute doit être supérieur à :value characters.',
        'array' => ':attribute doit avoir plus de :value objets.',
    ],
    'gte' => [
        'numeric' => ':attribute doit être supérieur ou égal :value.',
        'file' => ':attribute doit être supérieur ou égal :value kilobytes.',
        'string' => ':attribute doit être supérieur ou égal :value caractères.',
        'array' => ':attribute doit avoir :value objets ou plus.',
    ],
    'image' => ':attribute doit être une image.',
    'in' => 'La sélection :attribute est invalide.',
    'in_array' => "Le champ :attribute n'existe pas dans :other.",
    'integer' => ':attribute doit être un entier.',
    'ip' => ':attribute doit être une adresse IP valide.',
    'ipv4' => ':attribute doit être une adresse IPv4 valide.',
    'ipv6' => ':attribute doit être une adresse IPv6 valide.',
    'json' => ':attribute doit être une chaîne JSON valide.',
    'lt' => [
        'numeric' => ':attribute doit être inférieur à :value.',
        'file' => ':attribute doit être inférieur à :value kilobytes.',
        'string' => ':attribute doit être inférieur à :value caractères.',
        'array' => ':attribute doit avoir moins de :value objets.',
    ],
    'lte' => [
        'numeric' => ':attribute doit être inférieur ou égal :value.',
        'file' => ':attribute doit être inférieur ou égal :value kilobytes.',
        'string' => ':attribute doit être inférieur ou égal :value caractères.',
        'array' => ':attribute ne doit pas avoir plus de :value objets.',
    ],
    'max' => [
        'numeric' => ':attribute ne peut pas être supérieur à :max.',
        'file' => ':attribute ne peut pas être supérieur à :max kilobytes.',
        'string' => ':attribute ne peut pas être supérieur à :max caractères.',
        'array' => ':attribute peut ne pas avoir plus de :max objets.',
    ],
    'mimes' => ':attribute doit être un fichier de type: :values.',
    'mimetypes' => ':attribute doit être un fichier de type: :values.',
    'min' => [
        'numeric' => ':attribute doit être au moins :min.',
        'file' => ':attribute doit être au moins :min kilobytes.',
        'string' => ':attribute doit être au moins :min caractères.',
        'array' => ':attribute doit avoir au moins :min objets.',
    ],
    'not_in' => 'La sélection :attribute est invalide.',
    'not_regex' => 'Le format :attribute est invalide.',
    'numeric' => ':attribute must be a number.',
    'present' => 'Le champ :attribute doit être présent.',
    'regex' => 'Le format :attribute est invalide.',
    'required' => 'Le champ :attribute est requis.',
    'required_if' => 'Le champ :attribute est requis quand :other est :value.',
    'required_unless' => 'Le champ :attribute est requis sauf :other est dans :values.',
    'required_with' => 'Le champ :attribute est requis lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est requis quand :values est présent.',
    'required_without' => "Le champ :attribute est requis quand :values n'est pas present.",
    'required_without_all' => 'Le champ :attribute est requis quand aucun des :values est présent.',
    'same' => ':attribute et :other doit correspondre.',
    'size' => [
        'numeric' => ':attribute doit être :size.',
        'file' => ':attribute doit être :size kilobytes.',
        'string' => ':attribute doit être :size caractères.',
        'array' => ':attribute doit contenir :size objets.',
    ],
    'starts_with' => ':attribute doit commencer par l’un des éléments suivants: :values',
    'string' => ':attribute doit être une chaîne.',
    'timezone' => ':attribute doit être une zone valide.',
    'unique' => ':attribute a déjà été pris.',
    'uploaded' => ':attribute échec du téléchargement.',
    'url' => 'Le format :attribute est invalide.',
    'uuid' => ':attribute doit être un UUID valide.',

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
