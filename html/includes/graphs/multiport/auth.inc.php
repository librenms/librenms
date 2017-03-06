<?php

foreach (explode(',', $vars['id']) as $ifid) {
    if ($auth || port_permitted($ifid)) {
        $auth = true;
    }
}

$title = 'Multi Port :: ';
