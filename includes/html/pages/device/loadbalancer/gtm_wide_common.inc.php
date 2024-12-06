<?php
/*
 * LibreNMS module to display F5 GTM Wide IP Details
 *
 * Adapted from F5 LTM module by Darren Napper
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Pages
$subtypes = [];
$subtypes['gtm_wide_det'] = 'Wide IP Details';
// If we have a defautl pool, display the details.
if ($vars['poolid'] != 0) {
    $subtypes['gtm_wide_pool'] = 'Default Pool Details';
}

if (! $vars['subtype']) {
    $vars['subtype'] = 'gtm_wide_det';
}

// Determine a policy to show.
if (! isset($vars['wideid'])) {
    foreach ($components as $id => $array) {
        if ($array['type'] != 'f5-gtm-wide') {
            continue;
        }
        $vars['wideid'] = $id;
    }
}

print_optionbar_start();
?>
    <div class='row' style="margin-bottom: 10px;">
        <div class='col-md-12'>
            <span style="font-size: 20px;">Wide IP - <?php echo $components[$vars['wideid']]['label']?></span><br />
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
<?php

// Pages, on the left.
$sep = '';
foreach ($subtypes as $page => $text) {
    echo $sep;
    if ($vars['subtype'] == $page) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, ['subtype' => $page]);
    if ($vars['subtype'] == $page) {
        echo '</span>';
    }

    $sep = ' | ';
}
unset($sep);

?>
        </div>
    </div>
<?php
print_optionbar_end();
