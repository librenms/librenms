<?php

header('Content-type: application/json');

// reload dashboard referencing old endpoint
exit(json_encode([
    'html' => '<javascript>window.location.reload(false);</javascript>',
    'status' => 'ok',
    'title' => 'Reload Dasbhoard'
]));
