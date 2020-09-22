<?php
/*
 * LibreNMS module to capture statistics from the CISCO-NTP-MIB
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$options = [];
$options['filter']['ignore'] = ['=', 0];
$options['type'] = 'ntp';
$components = $component->getComponents(null, $options);

print_optionbar_start();

$view_options = [
    'all'       => 'All',
    'error'     => 'Error',
];
if (! $vars['view']) {
    $vars['view'] = 'all';
}

$graph_options = [
    'none'          => 'No Graphs',
    'stratum'       => 'Stratum',
    'offset'        => 'Offset',
    'delay'         => 'Delay',
    'dispersion'    => 'Dispersion',
];
if (! $vars['graph']) {
    $vars['graph'] = 'none';
}

echo '<span style="font-weight: bold;">NTP Peers</span> &#187; ';

// The menu option - on the left
$sep = '';
foreach ($view_options as $option => $text) {
    if (empty($vars['view'])) {
        $vars['view'] = $option;
    }
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }
    echo generate_link($text, $vars, ['view' => $option]);
    if ($vars['view'] == $option) {
        echo '</span>';
    }
    $sep = ' | ';
}

// The status option - on the right
echo '<div class="pull-right">';
$sep = '';
foreach ($graph_options as $option => $text) {
    if (empty($vars['graph'])) {
        $vars['graph'] = $option;
    }
    echo $sep;
    if ($vars['graph'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, ['graph' => $option]);
    if ($vars['graph'] == $option) {
        echo '</span>';
    }
    $sep = ' | ';
}
unset($sep);
echo '</div>';
print_optionbar_end();

?>
<table id='ntp-table' class='table table-condensed table-responsive table-striped'>
    <thead>
    <tr>
        <th data-column-id="device">Device</th>
        <th data-column-id="peer">Peer</th>
        <th data-column-id="stratum" data-type="numeric">Stratum</th>
        <th data-column-id="error">Error</th>
    </tr>
    </thead>
</table>
<script>
    $("#ntp-table").bootgrid({
        ajax: true,
        post: function ()
        {
            return {
                id: "app_ntp",
                view: '<?php echo $vars['view']; ?>',
                graph: '<?php echo $vars['graph']; ?>',
            };
        },
        url: "ajax_table.php",
    });
</script>
