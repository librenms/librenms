<?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'stp',
];

print_optionbar_start();

echo "<span style='font-weight: bold;'>STP</span> &#187; ";

if (! $vars['view']) {
    $vars['view'] = 'basic';
}

$menu_options['basic'] = 'Basic';
$menu_options['ports'] = 'Ports';
$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $link_array, ['view' => $option]);
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

if ($vars['view'] == 'basic') {
    include 'includes/html/print-stp.inc.php';
}

if ($vars['view'] == 'ports') {
    include 'includes/html/common/stp-ports.inc.php';
    echo implode('', $common_output);
}

$pagetitle[] = 'STP';
