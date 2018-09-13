<?php

return [

    /*
     * Set trusted proxy IP addresses.
     *
     * Both IPv4 and IPv6 addresses are
     * supported, along with CIDR notation.
     *
     * The "*" character is syntactic sugar
     * within TrustedProxy to trust any proxy
     * that connects directly to your server,
     * a requirement when you cannot know the address
     * of your proxy (e.g. if using ELB or similar).
     *
     */
    'proxies' => \LibreNMS\Util\Env::parseArray('APP_TRUSTED_PROXIES', '*', ['', '*', '**']),

    /*
     * To trust one or more specific proxies that connect
     * directly to your server, use an array of IP addresses:
     */
     # 'proxies' => ['192.168.1.1'],

    /*
     * Or, to trust all proxies that connect
     * directly to your server, use a "*"
     */
     # 'proxies' => '*',

    /*
     * Which headers to use to detect proxy related data (For, Host, Proto, Port)
     * 
     * Options include:
     * 
     * - Illuminate\Http\Request::HEADER_X_FORWARDED_ALL (use all x-forwarded-* headers to establish trust)
     * - Illuminate\Http\Request::HEADER_FORWARDED (use the FORWARDED header to establish trust)
     * 
     * @link https://symfony.com/doc/current/deployment/proxies.html
     */
    'headers' => Illuminate\Http\Request::HEADER_X_FORWARDED_ALL,
//    'headers' => [
//        (defined('Illuminate\Http\Request::HEADER_FORWARDED') ? Illuminate\Http\Request::HEADER_FORWARDED : 'forwarded') => 'FORWARDED',
//        \Illuminate\Http\Request::HEADER_CLIENT_IP    => 'X_FORWARDED_FOR',
//        \Illuminate\Http\Request::HEADER_CLIENT_HOST  => 'X_FORWARDED_HOST',
//        \Illuminate\Http\Request::HEADER_CLIENT_PROTO => 'X_FORWARDED_PROTO',
//        \Illuminate\Http\Request::HEADER_CLIENT_PORT  => 'X_FORWARDED_PORT',
//    ]
    
];
