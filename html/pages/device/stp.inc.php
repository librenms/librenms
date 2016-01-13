<?php

$link_array = array(
               'page'   => 'device',
               'device' => $device['device_id'],
               'tab'    => 'stp',
              );

print_optionbar_start();

echo "<span style='font-weight: bold;'>STP</span> &#187; ";

if (!$vars['view']) {
    $vars['view'] = 'basic';
}

$menu_options['basic'] = 'Basic';
// $menu_options['details'] = 'Details';
$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $link_array, array('view' => $option));
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

echo '<table border="0" cellspacing="0" cellpadding="5" width="100%">';

$i = '1';

foreach (dbFetchRows("SELECT * FROM `stp` WHERE `device_id` = ? ORDER BY 'stp_id'", array($device['device_id'])) as $stp) {
    include 'includes/print-stp.inc.php';

    $i++;
}

echo '</table>';

$pagetitle[] = 'STP';
