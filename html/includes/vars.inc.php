<?php

foreach ($_GET as $key => $get_var) {
    if (strstr($key, 'opt')) {
        list($name, $value) = explode('|', $get_var);
        if (!isset($value)) {
            $value = 'yes';
        }

        $vars[$name] = $value;
    }
}

$segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$base_url = parse_url($config["base_url"]);
$segments = explode('/', trim(str_replace($base_url["path"], "", $_SERVER['REQUEST_URI']), '/'));

foreach ($segments as $pos => $segment) {
    $segment = urldecode($segment);
    if ($pos == '0') {
        $vars['page'] = $segment;
    }
    else {
        list($name, $value) = explode('=', $segment);
        if ($value == '' || !isset($value)) {
            $vars[$name] = yes;
        }
        else {
            $vars[$name] = $value;
        }
    }
}

foreach ($_GET as $name => $value) {
    $vars[$name] = $value;
}

foreach ($_POST as $name => $value) {
    $vars[$name] = $value;
}
