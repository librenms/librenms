<?php
/*
 * LibreNMS module to Display data from F5 BigIP LTM Devices
 *
 * Copyright (c) 2019 Yacine BENAMSILI <https://github.com/yac01/ yacine.benamsili@homail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
// Pages
$subtypes = [];
$subtypes['ltm_bwc_det'] = 'Bandwidth Controller Details';

if (! $vars['subtype']) {
    $vars['subtype'] = 'ltm_bwc_det';
}

// Determine a policy to show.
if (! isset($vars['bwcid'])) {
    foreach ($components as $id => $array) {
        if ($array['type'] != 'f5-ltm-bwc') {
            continue;
        }
        $vars['bwcid'] = $id;
    }
}

print_optionbar_start();
?>
    <div class='row' style="margin-bottom: 10px;">
        <div class='col-md-12'>
            <span style="font-size: 20px;">Bandwidth Controller - <?php echo $components[$vars['bwcid']]['label'] ?></span><br /> 
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
        </div>
    </div>
<?php
print_optionbar_end();
