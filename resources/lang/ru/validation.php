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

    'accepted' => 'Вы подтвердить :attribute.',
    'active_url' => 'Данная :attribute ссылка не является валидным URL.',
    'after' => ':attribute должна быть дата больше чем :date.',
    'after_or_equal' => ':attribute должна быть дата больше или равная чем :date.',
    'alpha' => ':attribute может содержать только буквы.',
    'alpha_dash' => ':attribute может содержать только буквы, числа тире и подчекивания.',
    'alpha_num' => ':attribute ожет содержать только буквыи числа.',
    'array' => ':attribute должен быть массивом.',
    'before' => ':attribute должна быть дыты меньше чем  :date.',
    'before_or_equal' => ':attribute должна быть меньше или равно to :date.',
    'between' => [
        'numeric' => ':attribute должен быть в интервале между :min и :max.',
        'file' => ':attribute размер должен составлять от :min до :max kilobytes.',
        'string' => ':attribute должно быть от :min до :max символов.',
        'array' => ':attribute долже содержать от  :min до :max элементов.',
    ],
    'boolean' => ':attribute поле может иметь значения true или false.',
    'confirmed' => ':attribute не совпадает с подтверждением.',
    'date' => ':attribute даиа не валидна.',
    'date_equals' => ':attribute дата должна соответствовать :date.',
    'date_format' => ':attribute не совпадает формат с :format.',
    'different' => ':attribute и :other должны отличаться друг от друга.',
    'digits' => ':attribute должен содержать :digits чисел.',
    'digits_between' => ':attribute должен содержать не менее :min и  не более :max чисел.',
    'dimensions' => ':attribute не рисунок.',
    'distinct' => ':attribute поле содержит повторения.',
    'email' => ':attribute должен быть валидным email адресом.',
    'exists' => 'выбран не верный параметр: :attribute.',
    'file' => ':attribute должен быть файлом.',
    'filled' => ':attribute поле должно иметь значения.',
    'gt' => [
        'numeric' => ':attribute должно быть больше :value.',
        'file' => ':attribute должен быть больше :value kilobytes.',
        'string' => ':attribute должен иметь больше :value символов.',
        'array' => ':attribute должен содержать не менее:value элементов.',
    ],
    'gte' => [
        'numeric' => ':attribute должно быть больше или равно :value.',
        'file' => ':attribute должно быть больше или равно :value kilobytes.',
        'string' => ':attribute должно быть больше или равно :value символов.',
        'array' => ':attribute должно быть больше или равно :value элементов.',
    ],
    'image' => ':attribute должно быть рисунком.',
    'in' => 'Вы выбрали не верный :attribute.',
    'in_array' => ':attribute не относиться :other.',
    'integer' => ':attribute должен быть цислом.',
    'ip' => ':attribute должен быть правильным IP адресом.',
    'ipv4' => ':attribute должен быть правильным IPv4 адресом.',
    'ipv6' => ':attribute должен быть правильным IPv6 адресом.',
    'json' => ':attribute должен быть правильным JSON.',
    'lt' => [
        'numeric' => ':attribute должен быть меньше чем :value.',
        'file' => ':attribute должен быть меньше чем  :value kilobytes.',
        'string' => ':attribute должен быть меньше чем :value символов.',
        'array' => ':attribute должен быть меньше чем  :value элементов.',
    ],
    'lte' => [
        'numeric' => ':attribute должен быть меньше или равен :value.',
        'file' => ':attribute должен быть меньше или равен  :value kilobytes.',
        'string' => ':attribute должен быть меньше или равен  :value символам.',
        'array' => ':attribute должен быть меньше или равен  :value элементам.',
    ],
    'max' => [
        'numeric' => ':attribute не может быть больше :max.',
        'file' => ':attribute не может быть больше :max kilobytes.',
        'string' => ':attribute не может быть больше :max символов.',
        'array' => ':attribute не может быть больше :max элементов.',
    ],
    'mimes' => ':attribute должен соответствовать типу: :values.',
    'mimetypes' => ':attribute должны соответствовать типу: :values.',
    'min' => [
        'numeric' => ':attribute должен быть меньше :min.',
        'file' => ':attribute должен быть меньше :min kilobytes.',
        'string' => ':attribute должен быть меньше :min символов.',
        'array' => ':attribute должен быть меньше :min элементов.',
    ],
    'not_in' => 'Выбран не верный :attribute.',
    'not_regex' => ':attribute имеет не верный формат.',
    'numeric' => ':attribute должен быть числом.',
    'present' => ':attribute поле должно быть заполнено.',
    'regex' => ':attribute не верный формат.',
    'required' => ':attribute поля обязательно к заполнению.',
    'required_if' => ':attribute обязательно к заполнению :other если :value.',
    'required_unless' => ':attribute обязательно к заполнению если :other содержить :values.',
    'required_with' => ':attribute обязательно к заполению если :values присутствует.',
    'required_with_all' => ':attribute надо заполнить если :values заполнены.',
    'required_without' => ':attribute надо заполнить если :values отсутствуют.',
    'required_without_all' => ':attribute надо заполнить если нет ни одного :values ',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => ':attribute должен иметь :size.',
        'file' => ':attribute должен иметь :size kilobytes.',
        'string' => ':attribute должен иметь :size символов.',
        'array' => ':attribute должен иметь :size элементов.',
    ],
    'starts_with' => ':attribute должен начинаться с: :values',
    'string' => ':attribute должен быть строкой.',
    'timezone' => ':attribute не верный часовой пояс.',
    'unique' => ':attribute уже используется.',
    'uploaded' => ':attribute не удалось загрузить.',
    'url' => ':attribute не верный фомат URL.',
    'uuid' => ':attribute должен иметь правильный UUID.',

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
