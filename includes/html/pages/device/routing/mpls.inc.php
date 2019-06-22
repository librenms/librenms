<?php

print_optionbar_start();

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'routing',
    'proto'  => 'mpls',
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
    echo '<tr><th><a title="Administrative name for this Labeled Switch Path">Name</a></th>
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

    foreach (dbFetchRows('SELECT *, `vrf_name` FROM `mpls_lsps` AS l, `vrfs` AS v WHERE `l`.`vrf_oid` = `v`.`vrf_oid` AND `l`.`device_id` = `v`.`device_id` AND `l`.`device_id` = ?  ORDER BY `l`.`mplsLspName`', array($device['device_id'])) as $lsp) {
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
        } elseif ($lsp['mplsLspAdminState'] == 'inService' && $lsp['mplsLspOperState'] == 'outOfService') {
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
            <td>" . $lsp['mplsLspName'] . '</td>
            <td>' . $destination . '</td>
            <td>' . $lsp['vrf_name'] . '</td>
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
    echo '<tr><th><a title="Administrative name for LSP this path belongs to">LSP Name</a></th>
        <th><a title="The OID index of this path">Index</a></th>
        <th><a title="This variable is an enum that represents the role this path is taking within this LSP.">Type</a></th>
        <th><a title="The desired administrative state for this LSP Path.">Admin State</a></th>
        <th><a title="The current operational state of this LSP Path.">Oper State</a></th>
        <th><a title="The sysUpTime when this LSP Path was last modified.">Last Change at</a></th>
        <th><a title="The number of transitions that have occurred for this LSP.">Transitions</a></th>
        <th><a title="This value specifies the amount of bandwidth in megabits per seconds (Mbps) to be reserved for this LSP path. A value of zero (0) indicates that no bandwidth is reserved.">Bandwidth</a></th>
        <th><a title="When make-before-break functionality for the LSP is enabled and if the path bandwidth is changed, the resources allocated to the existing LSP paths will not be released until a new path with the new bandwidth settings has been established. While a new path is being signaled, the administrative value and the operational values of the path bandwidth may differ.">Oper BW</a></th>
        <th><a title="The current working state of this path within this LSP.">State</a></th>
        <th><a title="This indicates the reason code for LSP path failure. A value of 0 indicates that no failure has occurred.">Failcode</a></th>
        <th><a title="This indicates the name of the node in the LSP path at which the LSP path failed.">Fail Node</a></th>
        <th><a title="This indicates the cost of the traffic engineered path returned by the IGP.">Metric</a></th>
        <th><a title="This indicates the operational metric for the LSP path.">Oper Metric</a></th>
        </tr>';

    $i = 0;

    foreach (dbFetchRows('SELECT *, `mplsLspName` FROM `mpls_lsp_paths` AS `p`, `mpls_lsps` AS `l` WHERE `p`.`lsp_id` = `l`.`lsp_id` AND `p`.`device_id` = ?  ORDER BY `l`.`mplsLspName`', array($device['device_id'])) as $path) {
        if (!is_integer($i / 2)) {
            $bg_colour = $config['list_colour']['even'];
        } else {
            $bg_colour = $config['list_colour']['odd'];
        }

        $adminstate_status_color = $operstate_status_color = 'default';
        $failcode_status_color = 'warning';

        if ($path['mplsLspPathAdminState'] == 'inService') {
            $adminstate_status_color = 'success';
        }
        if ($path['mplsLspPathFailCode'] == 'noError') {
            $failcode_status_color = 'success';
        }
        if ($path['mplsLspPathOperState'] == 'inService') {
            $operstate_status_color = 'success';
        } elseif ($path['mplsLspPathAdminState'] == 'inService' && $path['mplsLspPathOperState'] == 'outOfService') {
            $operstate_status_color = 'danger';
        }
       
        $host = @dbFetchRow('SELECT * FROM `ipv4_addresses` AS A, `ports` AS I, `devices` AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$path['mplsLspPathFailNodeAddr']]);
        $destination = $lsp['mplsLspPathFailNodeAddr'];
        if (is_array($host)) {
            $destination = generate_device_link($host, 0, array('tab' => 'routing', 'proto' => 'mpls'));
        }
        echo "<tr bgcolor=$bg_colour>
            <td>" . $path['mplsLspName'] . '</td>
            <td>' . $path['path_oid'] . '</td>
            <td>' . $path['mplsLspPathType'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $path['mplsLspPathAdminState'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $path['mplsLspPathOperState'] . '</td>
            <td>' . formatUptime($path['mplsLspPathLastChange']) . '</td>
            <td>' . $path['mplsLspPathTransitionCount'] . '</td>
            <td>' . $path['mplsLspPathBandwidth'] . '</td>
            <td>' . $path['mplsLspPathOperBandwidth'] . '</td>
            <td>' . $path['mplsLspPathState'] . '</td>
            <td><span class="label label-' . $failcode_status_color . '">' . $path['mplsLspPathFailCode'] . '</td>
            <td>' . $destination . '</td>
            <td>' . $path['mplsLspPathMetric'] . '</td>
            <td>' . $path['mplsLspPathOperMetric'] . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end lsp path view
