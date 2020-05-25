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

    'accepted' => ':attribute має бути прийнято.',
    'active_url' => ':attribute не є дійсним URL.',
    'after' => ':attribute має бути датою пізнішою за :date.',
    'after_or_equal' => ':attribute має бути датою не ранішою за :date.',
    'alpha' => ':attribute має містити лише літери.',
    'alpha_dash' => ':attribute може містити лише літери, цифри, тире та підкреслення.',
    'alpha_num' => ':attribute може містити лише літери та цифри.',
    'array' => ':attribute має бути масивом.',
    'before' => ':attribute має бути датою ранішою за :date.',
    'before_or_equal' => ':attribute має бути датою не пізнішою за :date.',
    'between' => [
        'numeric' => ':attribute має бути у проміжку між :min та :max.',
        'file' => ':attribute має бути у проміжку між :min та :max кілобайт.',
        'string' => ':attribute має містити від :min до :max символів.',
        'array' => ':attribute має містити від :min до :max елементів.',
    ],
    'boolean' => ':attribute має бути true чи false.',
    'confirmed' => 'Підтвердження :attribute не співпадає.',
    'date' => ':attribute не є коректною датою.',
    'date_equals' => ':attribute має бути рівним :date.',
    'date_format' => ':attribute не співпадає з форматом :format.',
    'different' => ':attribute та :other повинні мати різні значення.',
    'digits' => ':attribute має містити :digits цифр.',
    'digits_between' => ':attribute має містити від :min до :max цифр.',
    'dimensions' => ':attribute містить некоректні виміри зображення.',
    'distinct' => 'Поле :attribute містить однакове значення.',
    'email' => ':attribute має бути дійсною адресою електронної пошти.',
    'exists' => 'Обраний :attribute не є валідним.',
    'file' => ':attribute має бути файлом.',
    'filled' => 'Поле :attribute повинне мати значення.',
    'gt' => [
        'numeric' => ':attribute має бути більшим за :value.',
        'file' => ':attribute має бути більшим за :value кілобайт.',
        'string' => ':attribute має бути довшим за :value символів.',
        'array' => ':attribute повинен містити більше ніж :value елементів.',
    ],
    'gte' => [
        'numeric' => ':attribute має бути не меншим за :value.',
        'file' => ':attribute має бути не меншим за :value кілобайт.',
        'string' => ':attribute має бути не коротшим за :value символів.',
        'array' => ':attribute повинен містити не менше ніж :value елементів.',
    ],
    'image' => ':attribute має бути зображенням.',
    'in' => 'Обраний :attribute не є валідним.',
    'in_array' => 'Поле :attribute не існує у :other.',
    'integer' => ':attribute має бути типу integer.',
    'ip' => ':attribute має бути валідною IP адресою.',
    'ipv4' => ':attribute має бути валідною IPv4.',
    'ipv6' => ':attribute має бути валідною IPv6 адресою.',
    'json' => ':attribute має бути валідним JSON.',
    'lt' => [
        'numeric' => ':attribute має бути меншим за :value.',
        'file' => ':attribute має бути меншим за :value кілобайт.',
        'string' => ':attribute має бути коротшим за :value символів.',
        'array' => ':attribute повинен містити менше ніж :value елементів.',
    ],
    'lte' => [
        'numeric' => ':attribute має бути не більшим за :value.',
        'file' => ':attribute має бути не більшим за :value кілобайт.',
        'string' => ':attribute має бути не довшим за :value символів.',
        'array' => ':attribute повинен містити не більше ніж :value елементів.',
    ],
    'max' => [
        'numeric' => ':attribute не може бути більшим за :max.',
        'file' => ':attribute не може бути більшим за :max кілобайт.',
        'string' => ':attribute не може бути довшим за :max символів.',
        'array' => ':attribute не може мати більше ніж :max елементів.',
    ],
    'mimes' => ':attribute має бути файлом типу: :values.',
    'mimetypes' => ':attribute має бути файлом типу: :values.',
    'min' => [
        'numeric' => ':attribute має бути щонайменше :min.',
        'file' => ':attribute має бути щонайменше :min кілобайт.',
        'string' => ':attribute має бути щонайменше :min символів.',
        'array' => ':attribute має містити щонайменше :min елементів.',
    ],
    'not_in' => 'Обраний :attribute не валідний.',
    'not_regex' => 'Формат :attribute не валідний.',
    'numeric' => ':attribute має бути числом.',
    'present' => 'Поле :attribute має бути наявним.',
    'regex' => 'Формат :attribute не валідний.',
    'required' => 'Необхідне поле :attribute.',
    'required_if' => 'Поле :attribute необхідне коли :other має значення :value.',
    'required_unless' => 'Поле :attribute необхідне, окрім випадків коли :other має значення :values.',
    'required_with' => 'Поле :attribute необхідне при наявності одного з :values.',
    'required_with_all' => 'Поле :attribute необхідне при наявності усіх перерахованих :values.',
    'required_without' => 'Поле :attribute необхідне за відсутності одного з :values.',
    'required_without_all' => 'Поле :attribute необхідне за відсутності усіх перерахованих :values.',
    'same' => ':attribute та :other повинні співпадати.',
    'size' => [
        'numeric' => ':attribute має бути :size.',
        'file' => ':attribute має бути :size кілобайт.',
        'string' => ':attribute повинен складати :size символів.',
        'array' => ':attribute повинен містити :size елементів.',
    ],
    'starts_with' => ':attribute повинен починатися з одного з наступних: :values',
    'string' => ':attribute має бути типу string.',
    'timezone' => ':attribute має бути валідною часовою зоною.',
    'unique' => ':attribute вже призначений.',
    'uploaded' => ':attribute не було завантажено успішно.',
    'url' => 'Формат :attribute є не валідним.',
    'uuid' => ':attribute має бути валідним UUID.',

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
