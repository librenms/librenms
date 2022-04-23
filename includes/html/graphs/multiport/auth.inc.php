<?php

if (! $auth) {
    foreach (explode(',', $vars['id']) as $ifid) {
        $auth = port_permitted($ifid);
        if (! $auth) {
            break;
        }
    }
}

$title = 'Multi Port :: ';
