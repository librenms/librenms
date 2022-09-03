<?php

$vars = \LibreNMS\Util\Url::parseLegacyPathVars($_SERVER['REQUEST_URI']);

foreach ($_GET as $name => $value) {
    $vars[$name] = strip_tags($value);
}

foreach ($_POST as $name => $value) {
    $vars[$name] = ($value);
}

// don't leak login data
unset($vars['username'], $vars['password'], $uri, $base_url);
