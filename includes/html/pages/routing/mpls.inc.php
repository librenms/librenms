<?php

print_optionbar_start();

$link_array = array(
    'page'    => 'routing',
    'protocol'  => 'mpls',
);

if (!isset($vars['view'])) {
    $vars['view'] = 'lsp';
}

echo '<span style="font-weight: bold;">MPLS</span> &#187; ';

if ($vars['view'] == 'lsp') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('LSP', $link_array, array('view' => 'lsp'));
if ($vars['view'] == 'lsp') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'paths') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Paths', $link_array, array('view' => 'paths'));
if ($vars['view'] == 'paths') {
    echo '</span>';
}

print_optionbar_end();

echo '<div id="content">
    <table  border="0" cellspacing="0" cellpadding="5" width="100%">';
if ($vars['view'] == 'lsp') {
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="Administrative name for this Labeled Switch Path">Name</a></th>
        <th><a title="Specifies the destination address of this LSP">Destination</a></th>
        <th><a title="Virtual Routing Instance">VRF</a></th>
        <th><a title="The desired administrative state for this LSP.">Admin State</a></th>
        <th><a title="The current operational state of this LSP.">Oper State</a></th>
        <th><a title="The sysUpTime when this LSP was last modified.">Last Change at</a></th>
        <th><a title="The number of state transitions (up -> down and down -> up) this LSP has undergone.">Transitions</a></th>
        <th><a title="The time since the last transition (up -> down and down -> up) occurred on this LSP.">Last Transition</a></th>
        <th><a title="The number of paths configured for this LSP / The number of standby paths configured for this LSP / The number of operational paths for this LSP. This includes the path currently active, as well as operational standby paths.">Paths</br>Conf / Stby / Oper</a></th>
        <th><a title="The value specifies whether the label value is statically or dynamically assigned or whether the LSP will be used exclusively for bypass protection.">Type</a></th>
        <th><a title="When the value of FRR is true, fast reroute is enabled.  A pre-computed detour LSP is created from each node in the primary path of this LSP.  In case of a failure of a link or LSP between two nodes, traffic is immediately rerouted on the pre-computed detour LSP thus avoiding packet loss.">FRR</a></th>
        <th><a title="The percentage up time is calculated by (LSP up time / LSP age * 100 %).">Availability</br>%</a></th>
        </tr>';

    $i = 0;

    foreach (dbFetchRows('SELECT * FROM `mpls_lsps` ORDER BY `device_id`, `mplsLspName`') as $lsp) {
        $vrf = dbFetchRow('SELECT * FROM `vrfs` WHERE device_id = ? AND `vrf_oid` = ?', array($lsp['device_id'], $lsp['vrf_oid']));
        $device = device_by_id_cache($lsp['device_id']);

        if (!is_integer($i / 2)) {
            $bg_colour = $config['list_colour']['even'];
        } else {
            $bg_colour = $config['list_colour']['odd'];
        }

        $adminstate_status_color = $operstate_status_color = $path_status_color = 'default';

        if ($lsp['mplsLspAdminState'] == 'inService') {
            $adminstate_status_color = 'success';
        }
        if ($lsp['mplsLspOperState'] == 'inService') {
            $operstate_status_color = 'success';
        } elseif ($lsp['mplsLspAdminState'] == 'inService' and $lsp['mplsLspOperState'] == 'outOfService') {
            $operstate_status_color = 'danger';
        }
        if ($lsp['mplsLspConfiguredPaths'] + $lsp['mplsLspStandbyPaths'] == $lsp['mplsLspOperationalPaths']) {
            $path_status_color = 'success';
        } elseif ($lsp['mplsLspOperationalPaths'] == '0') {
            $path_status_color = 'danger';
        } elseif ($lsp['mplsLspConfiguredPaths'] + $lsp['mplsLspStandbyPaths'] > $lsp['mplsLspOperationalPaths']) {
            $path_status_color = 'warning';
        }

        $avail = round($lsp['mplsLspPrimaryTimeUp'] / $lsp['mplsLspAge'] * 100, 5);

        $host = @dbFetchRow('SELECT * FROM `ipv4_addresses` AS A, `ports` AS I, `devices` AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$lsp['mplsLspToAddr']]);
        $destination = $lsp['mplsLspToAddr'];
        if (is_array($host)) {
            $destination = generate_device_link($host, 0, array('tab' => 'routing', 'proto' => 'mpls'));
        }

        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'mpls')) . '</td>
            <td>' . $lsp['mplsLspName'] . '</td>
            <td>' . $destination . '</td>
            <td>' . $vrf['vrf_name'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $lsp['mplsLspAdminState'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $lsp['mplsLspOperState'] . '</td>
            <td>' . formatUptime($lsp['mplsLspLastChange']) . '</td>
            <td>' . $lsp['mplsLspTransitions'] . '</td>
            <td>' . formatUptime($lsp['mplsLspLastTransition']) . '</td>
            <td><span class="label label-' . $path_status_color . '">' . $lsp['mplsLspConfiguredPaths'] . '      /     ' . $lsp['mplsLspStandbyPaths'] . ' / ' . $lsp['mplsLspOperationalPaths'] . '</td>
            <td>' . $lsp['mplsLspType'] . '</td>
            <td>' . $lsp['mplsLspFastReroute'] . '</td>
            <td>' . $avail . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // endif lsp view

if ($vars['view'] == 'paths') {
}
