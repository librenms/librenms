<?php

$vars = \LibreNMS\Util\Url::parseLegacyPathVars($_SERVER['REQUEST_URI'] ?? null);

foreach ($_GET as $name => $value) {
    if (is_array($value)) {
        $vars[$name] = array_map_recursive(function ($item) {
            return is_string($item) ? strip_tags($item) : $item;
        }, $value);
    } else {
        $vars[$name] = strip_tags($value);
    }
}

foreach ($_POST as $name => $value) {
    $vars[$name] = $value;
}

// don't leak login and other data
unset($vars['username'], $vars['password'], $vars['_token']);

if (! function_exists('array_map_recursive')) {
    function array_map_recursive(callable $callback, array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key] = is_array($value) ? array_map_recursive($callback, $value) : $callback($value);
        }

        return $result;
    }
}