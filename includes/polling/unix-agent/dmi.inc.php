<?php

$dmi = $agent_data['dmi'];
unset($agent_data['dmi']);

foreach (explode("\n", $dmi) as $line) {
    list($field,$contents)     = explode('=', $line, 2);
    $agent_data['dmi'][$field] = trim($contents);
}

unset($dmi);
