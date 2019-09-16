<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel translations path
    |--------------------------------------------------------------------------
    |
    | The default path where the translations are stored by Laravel.
    | Note: the path will be prepended to point to the App directory.
    |
    */

    'langPath' => '/resources/lang',

    /*
    |--------------------------------------------------------------------------
    | Laravel translation files
    |--------------------------------------------------------------------------
    |
    | You can choose which translation files to be generated.
    | Note: leave this empty for all the translation files to be generated.
    |
    */

    'langFiles' => [
        /*
        'pagination',
        'passwords'
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded files & folders
    |--------------------------------------------------------------------------
    |
    | Exclude translation files, generic files or folders you don't need.
    |
    */
    'excludes' => [
        /*
        'validation',
        'example.file',
        'example-folder',
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Output file
    |--------------------------------------------------------------------------
    |
    | The javascript path where I will place the generated file.
    | Note: the path will be prepended to point to the App directory.
    |
    */
    'jsPath' => '/resources/js/langs/',
    'jsFile' => '/resources/js/vue-i18n-locales.generated.js',

    /*
    |--------------------------------------------------------------------------
    | i18n library
    |--------------------------------------------------------------------------
    |
    | Specify the library you use for localization.
    | Options are vue-i18n or vuex-i18n.
    |
    */
    'i18nLib' => 'vue-i18n',

    /*
    |--------------------------------------------------------------------------
    | Output messages
    |--------------------------------------------------------------------------
    |
    | Specify if the library should show "written to" messages
    | after generating json files.
    |
    */
    'showOutputMessages' => false,
];
