<?php

$vars = \LibreNMS\Util\Url::parseLegacyPathVars($_SERVER['REQUEST_URI'] ?? null);

foreach ($_GET as $name => $value) {
    $vars[$name] = strip_tags($value);
}

foreach ($_POST as $name => $value) {
    $vars[$name] = $value;
}

// don't leak login and other data
unset($vars['username'], $vars['password'], $vars['_token']);
