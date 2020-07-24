<?php

header('Content-type: application/json');

// reload dashboard referencing old endpoint
exit(json_encode([
    'html' => '<script>window.location.reload(false);</script>',
    'status' => 'ok',
    'title' => 'Reload Dasbhoard',
]));
