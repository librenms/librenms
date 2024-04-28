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

    'accepted' => ':attribute 必须被接受。',
    'active_url' => ':attribute 不是一个有效的网址。',
    'after' => ':attribute 必须晚于 :date 日期。',
    'after_or_equal' => ':attribute 必须等于或晚于 :date 日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、数字、破折号和下划线。',
    'alpha_num' => ':attribute 只能包含字母和数字。',
    'array' => ':attribute 必须是一个数组。',
    'before' => ':attribute 必须早于 :date 日期。',
    'before_or_equal' => ':attribute 必须等于或早于 :date 日期。',
    'between' => [
        'numeric' => ':attribute 必须在 :min 和 :max 之间。',
        'file' => ':attribute 必须在 :min 到 :max 千字节之间。',
        'string' => ':attribute 必须在 :min 到 :max 个字符之间。',
        'array' => ':attribute 必须有 :min 到 :max 项之间。',
    ],
    'boolean' => ':attribute 字段必须为真或假。',
    'confirmed' => ':attribute 的确认不匹配。',
    'date' => ':attribute 不是一个有效的日期。',
    'date_equals' => ':attribute 必须是一个与 :date 相同的日期。',
    'date_format' => ':attribute 与格式 :format 不符。',
    'different' => ':attribute 和 :other 必须不同。',
    'digits' => ':attribute 必须是 :digits 位数字。',
    'digits_between' => ':attribute 必须在 :min 和 :max 位数字之间。',
    'dimensions' => ':attribute 的图片尺寸无效。',
    'distinct' => ':attribute 字段有重复值。',
    'email' => ':attribute 必须是一个有效的电子邮件地址。',
    'exists' => '所选的 :attribute 无效。',
    'file' => ':attribute 必须是一个文件。',
    'filled' => ':attribute 字段必须有值。',
    'gt' => [
        'numeric' => ':attribute 必须大于 :value。',
        'file' => ':attribute 必须大于 :value 千字节。',
        'string' => ':attribute 必须大于 :value 个字符。',
        'array' => ':attribute 必须有超过 :value 项。',
    ],
    'gte' => [
        'numeric' => ':attribute 必须大于或等于 :value。',
        'file' => ':attribute 必须大于或等于 :value 千字节。',
        'string' => ':attribute 必须大于或等于 :value 个字符。',
        'array' => ':attribute 必须至少有 :value 项。',
    ],
    'image' => ':attribute 必须是一张图片。',
    'in' => '所选的 :attribute 无效。',
    'in_array' => ':attribute 字段不在 :other 中存在。',
    'integer' => ':attribute 必须是一个整数。',
    'ip' => ':attribute 必须是一个有效的IP地址。',
    'ipv4' => ':attribute 必须是一个有效的IPv4地址。',
    'ipv6' => ':attribute 必须是一个有效的IPv6地址。',
    'json' => ':attribute 必须是一个有效的JSON字符串。',
    'lt' => [
        'numeric' => ':attribute 必须小于 :value。',
        'file' => ':attribute 必须小于 :value 千字节。',
        'string' => ':attribute 必须小于 :value 个字符。',
        'array' => ':attribute 必须少于 :value 项。',
    ],
    'lte' => [
        'numeric' => ':attribute 必须小于或等于 :value。',
        'file' => ':attribute 必须小于或等于 :value 千字节。',
        'string' => ':attribute 必须小于或等于 :value 个字符。',
        'array' => ':attribute 不得超过 :value 项。',
    ],
    'max' => [
        'numeric' => ':attribute 不能大于 :max。',
        'file' => ':attribute 不能大于 :max 千字节。',
        'string' => ':attribute 不能大于 :max 个字符。',
        'array' => ':attribute 不能多于 :max 项。',
    ],
    'mimes' => ':attribute 必须是以下类型文件: :values。',
    'mimetypes' => ':attribute 必须是以下类型文件: :values。',
    'min' => [
        'numeric' => ':attribute 必须至少为 :min。',
        'file' => ':attribute 必须至少为 :min 千字节。',
        'string' => ':attribute 必须至少为 :min 个字符。',
        'array' => ':attribute 至少必须有 :min 项。',
    ],
    'not_in' => '所选的 :attribute 无效。',
    'not_regex' => ':attribute 格式不正确。',
    'numeric' => ':attribute 必须是一个数字。',
    'present' => ':attribute 字段必须存在。',
    'regex' => ':attribute 格式不正确。',
    'required' => ':attribute 字段是必填的。',
    'required_if' => '当 :other 为 :value 时，:attribute 字段是必填的。',
    'required_unless' => '除非 :other 在 :values 中，否则 :attribute 字段是必填的。',
    'required_with' => '当 :values 存在时，:attribute 字段是必填的。',
    'required_with_all' => '当 :values 全部存在时，:attribute 字段是必填的。',
    'required_without' => '当 :values 不存在时，:attribute 字段是必填的。',
    'required_without_all' => '当没有 :values 存在时，:attribute 字段是必填的。',
    'same' => ':attribute 和 :other 必须一致。',
    'size' => [
        'numeric' => ':attribute 必须是 :size。',
        'file' => ':attribute 必须是 :size 千字节。',
        'string' => ':attribute 必须是 :size 个字符。',
        'array' => ':attribute 必须包含 :size 项。',
    ],
    'starts_with' => ':attribute 必须以以下之一开始: :values',
    'string' => ':attribute 必须是字符串类型。',
    'timezone' => ':attribute 必须是一个有效的时区。',
    'unique' => ':attribute 已经被占用。',
    'uploaded' => ':attribute 上传失败。',
    'url' => ':attribute 格式不正确。',
    'uuid' => ':attribute 必须是有效的UUID。',


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
