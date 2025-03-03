<?php

$no_refresh = true;

$pagetitle[] = 'Tools';

$sections = [
    'oxidized-cfg-check' => 'Oxidized Config Checker',
];

print_optionbar_start('', '');

echo '<span style="font-weight: bold;">Tools</span> &#187; ';

unset($sep);
foreach ($sections as $type => $texttype) {
    echo $sep;
    if ($vars['search'] == $type) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($texttype, ['page' => 'tools', 'tool' => $type]);

    if ($vars['search'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

$tools_type = basename($vars['tool']);
if (file_exists("includes/html/pages/tools/$tools_type.inc.php")) {
    include "includes/html/pages/tools/$tools_type.inc.php";
} else {
    echo report_this("Unknown tool type $tools_type");
}
