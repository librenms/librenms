<?php

use LibreNMS\Config;

foreach ($_GET as $key => $get_var) {
    if (strstr($key, 'opt')) {
        [$name, $value] = explode('|', $get_var);
        if (! isset($value)) {
            $value = 'yes';
        }

        $vars[$name] = strip_tags($value);
    }
}

$base_url = parse_url(Config::get('base_url'));
// don't parse the subdirectory, if there is one in the path
if (isset($base_url['path']) && strlen($base_url['path']) > 1) {
    $segments = explode('/', trim(str_replace($base_url['path'], '', $_SERVER['REQUEST_URI']), '/'));
} else {
    $segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
}

foreach ($segments as $pos => $segment) {
    $segment = urldecode($segment);
    if ($pos === 0) {
        $vars['page'] = $segment;
    } else {
        [$name, $value] = explode('=', $segment);
        if ($value == '' || ! isset($value)) {
            if ($vars['page'] == 'device' && $pos < 3) {
                // translate laravel device routes properly
                $vars[$pos === 1 ? 'device' : 'tab'] = $name;
            } else {
                $vars[$name] = 'yes';
            }
        } else {
            $vars[$name] = $value;
        }
    }
}

foreach ($_GET as $name => $value) {
    $vars[$name] = strip_tags($value);
}

foreach ($_POST as $name => $value) {
    $vars[$name] = ($value);
}

// don't leak login data
unset($vars['username'], $vars['password']);
