<?php

use Illuminate\Support\Arr;
use LibreNMS\Config;

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If array('*') is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => Config::get('api.cors.enabled') ? ['api/*'] : [],

    /*
    * Matches the request method. `[*]` allows all methods.
    */
    'allowed_methods' => Arr::wrap(Config::get('api.cors.allowmethods', ['*'])) ,

    /*
     * Matches the request origin. `[*]` allows all origins. Wildcards can be used, eg `*.mydomain.com`
     */
    'allowed_origins' => Arr::wrap(Config::get('api.cors.origin', ['*'])),

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     */
    'allowed_origins_patterns' => [],

    /*
     * Sets the Access-Control-Allow-Headers response header. `[*]` allows all headers.
     */
    'allowed_headers' => Arr::wrap(Config::get('api.cors.allowheaders', ['*'])),

    /*
     * Sets the Access-Control-Expose-Headers response header with these headers.
     */
    'exposed_headers' => Arr::wrap(Config::get('api.cors.exposeheaders', [])),

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     */
    'max_age' => (int)Config::get('api.cors.maxage', 86400),

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => (bool)Config::get('api.cors.allowcredentials'),
];
