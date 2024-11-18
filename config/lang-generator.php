<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation File Type
    |--------------------------------------------------------------------------
    |
    | It is possible to generate both json and array language files.
    | Possible values : array, json
    |
    */
    'file_type' => 'array',

    /*
    |--------------------------------------------------------------------------
    | Translation File Name
    |--------------------------------------------------------------------------
    |
    | A translation file will be created with the specified name if the array mode is selected
    |
    */
    'file_name' => 'lang',

    /*
    |--------------------------------------------------------------------------
    | Use short translation keys
    |--------------------------------------------------------------------------
    |
    | If the parser split keys at each dot.
    | ex: __('short.key') becomes [ 'short' => [ 'key '=> '' ] ]
    |
    */
    'short_keys' => true,

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | Array of supported languages of your application.
    | The specified folders will be created in the resource/lang folder
    |
    */
    'languages' => ['de', 'en', 'fr', 'it', 'pt-BR', 'ru', 'sr', 'uk', 'zh-CN', 'zh-TW'],
];
