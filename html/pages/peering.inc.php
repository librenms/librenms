<?php

print_optionbar_start();

// if (!$vars['protocol']) { $vars['protocol'] = "overview"; }
echo "<span style='font-weight: bold;'>Peering</span> &#187; ";

print_optionbar_end();

switch ($vars['section']) {
    case 'ix-list':
        require_once 'pages/peering/ix-list.inc.php';
        break;
    case 'ix-peers':
        require_once 'pages/peering/ix-peers.inc.php';
        break;
    default:
        require_once 'pages/peering/as-selection.inc.php';
}
