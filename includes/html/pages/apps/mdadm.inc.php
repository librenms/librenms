<?php

if (! isset($vars) || ! is_array($vars)) {
    return;
}

$vars['view'] ??= 'arrays';

echo view('apps.mdadm', ['vars' => $vars])->render();
