<?php

$auth = true;

foreach (explode(',', $vars['id']) as $ifid) {
    if (!$auth && !port_permitted($ifid)) {
        $auth = false;
    }
}

$title = 'Multi Port :: ';
