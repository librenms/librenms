<?php

$no_refresh = true;

$pagetitle[] = 'Search';

$sections = [
    'ipv4' => 'IPv4 Address',
    'ipv6' => 'IPv6 Address',
    'mac'  => 'MAC Address',
    'arp'  => 'ARP Table',
    'fdb'  => 'FDB Table',
];

if (dbFetchCell('SELECT 1 from `packages` LIMIT 1')) {
    $sections['packages'] = 'Packages';
}

$search_type = basename($vars['search'] ?? 'ipv4');

print_optionbar_start('', '');

echo '<span style="font-weight: bold;">Search</span> &#187; ';

unset($sep);
foreach ($sections as $type => $texttype) {
    echo $sep;
    if ($vars['search'] == $type) {
        echo "<span class='pagemenu-selected'>";
    }

    // echo('<a href="search/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $texttype .'</a>');
    echo generate_link($texttype, ['page' => 'search', 'search' => $type]);

    if ($vars['search'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

if (file_exists("includes/html/pages/search/$search_type.inc.php")) {
    include "includes/html/pages/search/$search_type.inc.php";
} else {
    echo report_this("Unknown search type $search_type");
}
