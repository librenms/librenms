<?php

$no_refresh = true;

$pagetitle[] = 'Tools';

$sections = array(
    'oxidized-cfg-check' => 'Oxidized Config Checker',
);

print_optionbar_start('', '');

echo '<span style="font-weight: bold;">Tools</span> &#187; ';

unset($sep);
foreach ($sections as $type => $texttype) {
    echo $sep;
    if ($vars['search'] == $type) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($texttype, array('page' => 'tools', 'tool' => $type));

    if ($vars['search'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

if (file_exists('pages/tools/'.$vars['tool'].'.inc.php')) {
    include 'pages/tools/'.$vars['tool'].'.inc.php';
} else {
    echo report_this('Unknown tool type '.$vars['tool']);
}
