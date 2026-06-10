<?php

if (! $auth) {
    foreach (explode(',', (string) $vars['id']) as $ifid) {
        $auth = port_permitted($ifid);
        if (! $auth) {
            break;
        }
    }
}

$title = 'Multi Port :: ';
