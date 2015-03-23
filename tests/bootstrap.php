<?php
return [
    "tcp" => [
        "host" => getenv('HOST'),
        "port" => getenv('TCP_PORT'),
        "protocol" => getenv('TCP_PROTOCOL'),
        "database" => getenv('TCP_DB'),
        "username" => getenv('USERNAME'),
        "password" =>  getenv('PASSWORD'),
    ],
    "udp" => [
        "host" => getenv('HOST'),
        "port" => getenv('UDP_PORT'),
        "database" => getenv('UDP_DB'),
        "username" => getenv('USERNAME'),
        "password" =>  getenv('PASSWORD'),
    ],
];
