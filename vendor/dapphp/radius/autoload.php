<?php

spl_autoload_register(function($class) {
    $parts = explode('\\', $class);

    if (sizeof($parts) == 1) {
        switch($class) {
            case 'Crypt_CHAP':
            case 'Crypt_CHAP_MD5':
            case 'Crypt_CHAP_MSv1':
            case 'Crypt_CHAP_MSv2':
                require __DIR__ . '/lib/Pear_CHAP.php';
                break;
        }
    } elseif (sizeof($parts) > 2) {
        if ($parts[0] == 'Dapphp' && $parts[1] == 'Radius') {
            require_once __DIR__ . '/src/' . $parts[2] . '.php';
        }
    }
});
