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

    'accepted' => ':attribute muss akzeptiert werden.',
    'active_url' => ':attribute ist keine gültige URL.',
    'after' => ':attribute muss später als :date sein.',
    'after_or_equal' => ':attribute muss dem :date oder später entsprechen.',
    'alpha' => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => ':attribute darf nur Buchstaben, Ziffern, Striche und Unterlinien enthalten.',
    'alpha_num' => ':attribute darf nur Buchstaben und Ziffern enthalten.',
    'alpha_space' => ':attribute darf nur Buchstaben, Ziffern Unterlinien und Leerzeichen enthalten.',
    'array' => ':attribute muss ein Array sein.',
    'before' => 'Th:attribute muss ein Datum vor dem :date sein.',
    'before_or_equal' => ':attribute muss ein Datum vor oder gleich dem :date sein.',
    'between' => [
        'numeric' => ':attribute muss zwischen :min und :max sein.',
        'file' => ':attribute muss zwischen :min und :max kilobytes sein.',
        'string' => ':attribute muss zwischen :min und :max Zeichen haben.',
        'array' => ':attribute muss mindestens :min und maximal :max Elemente haben.',
    ],
    'boolean' => 'das :attribute Feld darf nur true oder false sein.',
    'confirmed' => 'Die Prüfung von :attribute schlug fehl.',
    'date' => ':attribute enthält kein gültiges Datum.',
    'date_equals' => ':attribute muss ein gültiges Datum gleich dem :date sein.',

    'date_format' => ':attribute entpricht nicht dem Format :format.',
    'different' => ':attribute und :other müssen sich unterscheiden.',
    'digits' => ':attribute muss aus aus :digits Ziffern bestehen.',
    'digits_between' => ':attribute muss mindestens :min und maximal :max Ziffern enthalten.',
    'dimensions' => ':attribute hat ungültige Bild Abmessungen.',
    'distinct' => ':attribute enthält doppelte Werte.',
    'email' => ':attribute muss eine gültige E-Mailadresse enthalten.',
    'ends_with' => ':attribute muss mit einem der folgenden Werte enden: :values',
    'exists' => 'Die Auswahl :attribute ist ungültig.',
    'file' => ':attribute muss eine Datei sein.',
    'filled' => ':attribute darf nicht leer sein.',
    'gt' => [
        'numeric' => ':attribute muss zwischen :min und :max sein.',
        'file' => ':attribute muss zwischen :min und :max kilobytes sein.',
        'string' => ':attribute muss zwischen :min und :max Zeichen haben.',
        'array' => ':attribute muss mindestens :min und maximal :max Elemente haben.',
    ],
    'gte' => [
        'numeric' => ':attribute muss zwischen :min und :max sein.',
        'file' => ':attribute muss zwischen :min und :max kilobytes sein.',
        'string' => ':attribute muss zwischen :min und :max Zeichen haben.',
        'array' => ':attribute muss mindestens :min und maximal :max Elemente haben.',
    ],
    'image' => ':attribute muss ein Bild sein.',
    'in' => ':attribute ist ungültig.',
    'in_array' => ':attribute existiert nicht in :other.',
    'integer' => ':attribute muss ein Integer sein.',
    'ip' => ':attribute muss eine gültige IP address enthalten.',
    'ipv4' => ':attribute muss eine gültige IPv4 Addresse enthalten.',
    'ipv6' => ':attribute muss eine gültige IPv6 Addresse enthalten.',
    'json' => ':attribute muss einen gültigen JSON String enthalten.',
    'lt' => [
        'numeric' => ':attribute muss kleiner als :value sein.',
        'file' => ':attribute muss kleiner als :value kilobytes sein.',
        'string' => ':attribute muss weniger als :value Zeichen enthalten.',
        'array' => ':attribute muss weniger als :value Elemente haben.',
    ],
    'lte' => [
        'numeric' => ':attribute muss kleiner oder gleich :value sein.',
        'file' => ':attribute muss kleiner oder gleich :value kilobytes sein.',
        'string' => ':attribute muss weniger oder gleich :value Zeichen enthalten.',
        'array' => ':attribute muss weniger oder gleich :value Elemente haben.',
    ],
    'max' => [
        'numeric' => ':attribute darf nicht größer als :max sein.',
        'file' => ':attribute darf nicht größer als :max kilobytes sein.',
        'string' => ':attribute darf nicht mehr als :max Zeichen haben.',
        'array' => ':attribute darf nicht mehr als :max Elemente haben.',
    ],
    'mimes' => ':attribute muss eine Datei des Types :values sein.',
    'mimetypes' => ':attribute muss eine Datei des Types :values sein.',
    'min' => [
        'numeric' => ':attribute muss mindestens :min sein.',
        'file' => ':attribute muss mindestens :min kilobytes groß sein.',
        'string' => ':attribute muss mindestens :min Zeichen lang sein.',
        'array' => ':attribute muss mindestens :min Elemente enthalten.',
    ],
    'not_in' => 'Die Auswahl von :attribute ist ungültig.',
    'not_regex' => 'Das Format von :attribute ist ungültig.',
    'numeric' => ':attribute muss eine Ziffer sein.',
    'present' => ':attribute darf nicht leer sein.',
    'regex' => 'Das Format von :attribute ist ungültig.',
    'required' => ':attribute wird benötigt.',
    'required_if' => ':attribute wird benötigt wenn :other den Wert :value enthält.',
    'required_unless' => ':attribute wird benötigt außer :other enthält:values.',
    'required_with' => ':attribute wird benötigt wenn :values existiert.',
    'required_with_all' => ':attribute wird benötigt wenn :values existieren.',
    'required_without' => ':attribute wird benötigt wenn :values nicht existieren.',
    'required_without_all' => ':attribute wird benötigt wenn keines von :values existieren.',
    'same' => ':attribute und :other müssen passen.',
    'size' => [
        'numeric' => ':attribute muss :size groß sein.',
        'file' => ':attribute muss :size kilobyte groß sein.',
        'string' => ':attribute muss :size Zeichen groß sein.',
        'array' => ':attribute muss :size Elemente enthalten.',
    ],
    'starts_with' => ':attribute muss mit einem von diesen beginnen: :values',
    'string' => ':attribute muss einen Zeichenkette sein.',
    'timezone' => ':attribute muss eine gültige Zeitzone sein.',
    'unique' => ':attribute wird schon verwendet.',
    'uploaded' => 'Der Upload von :attribute schlug fehl.',
    'url' => 'Das Format von :attribute ist ungültig.',
    'uuid' => ':attribute muss eine gültige UUID sein.',

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
