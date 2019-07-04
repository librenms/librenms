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

echo generate_link('LSPs', $link_array, array('view' => 'lsp'));
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

echo ' | ';

if ($vars['view'] == 'sdps') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('SDPs', $link_array, array('view' => 'sdps'));
if ($vars['view'] == 'sdps') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'sdpbinds') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('SDP binds', $link_array, array('view' => 'sdpbinds'));
if ($vars['view'] == 'sdpbinds') {
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

    foreach (dbFetchRows('SELECT *, `vrf_name` FROM `mpls_lsps` AS l, `vrfs` AS v WHERE `l`.`vrf_oid` = `v`.`vrf_oid` AND `l`.`device_id` = `v`.`device_id` ORDER BY `l`.`device_id`, `l`.`mplsLspName`') as $lsp) {
        $device = device_by_id_cache($lsp['device_id']);

        if (!is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
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
            <td>" . generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'mpls')) . '</td>
            <td>' . $lsp['mplsLspName'] . '</td>
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
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="Administrative name for LSP this path belongs to">LSP Name</a></th>
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

    foreach (dbFetchRows('SELECT *, `mplsLspName` FROM `mpls_lsp_paths` AS `p`, `mpls_lsps` AS `l` WHERE `p`.`lsp_id` = `l`.`lsp_id` ORDER BY `p`.`device_id`, `l`.`mplsLspName`') as $path) {
        $device = device_by_id_cache($path['device_id']);
        if (!is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
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
            <td>" . generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'mpls')) . '</td>
            <td>' . $path['mplsLspName'] . '</td>
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
} // end paths view

if ($vars['view'] == 'sdps') {
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="Service Distribution Point identifier">SDP Id</a></th>
        <th><a title="The value of destination object specifies the device name of the remote end of the tunnel defined by this SDP">Destination</a></th>
        <th><a title="This object specifies the type of delivery used by this SDP">Type</a></th>
        <th><a title="The value of sdpActiveLspType indicates the type of LSP that is currently active on this SDP. For sdpDelivery gre, the value is always not-applicable. For sdpDelivery mpls, the values can be rsvp, ldp, mplsTp, srIsis, srOspf, srTeLsp, fpe, bgp or none.">LSP Type</a></th>
        <th><a title="Generic information about this SDP">Description</a></th>
        <th><a title="The desired administrative state for this SDP">Admin State</a></th>
        <th><a title="The current operational state of this SDP">Oper State</a></th>
        <th><a title="This object specifies the desired largest service frame size (in octets) that can be transmitted through this SDP to the far-end ESR, without requiring the packet to be fragmented. The default value of zero indicates that the path MTU should be computed dynamically from the corresponding MTU of the tunnel.">Admin MTU</a></th>
        <th><a title="This object indicates the actual largest service frame size (in octets) that can be transmitted through this SDP to the far-end ESR, without requiring the packet to be fragmented. In order to be able to bind this SDP to a given service, the value of this object minus the control word size (if applicable) must be equal to or larger than the MTU of the service, as defined by its svcMtu.">Oper MTU</a></th>
        <th><a title="The value of sysUpTime at the time of the most recent management-initiated change to this SDP.">Last Mgmt Change at</a></th>
        <th><a title="The value of sysUpTime at the time of the most recent operating status change to this SDP.">Last Status Change at</a></th>
        </tr>';

    $i = 0;

    foreach (dbFetchRows('SELECT * FROM `mpls_sdps` ORDER BY `sdp_oid') as $sdp) {
        $device = device_by_id_cache($sdp['device_id']);
        if (!is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        $adminstate_status_color = $operstate_status_color = 'default';
        $failcode_status_color = 'warning';

        if ($sdp['sdpAdminStatus'] == 'up') {
            $adminstate_status_color = 'success';
        }
        if ($sdp['sdpOperStatus'] == 'up') {
            $operstate_status_color = 'success';
        } elseif ($sdp['sdpAdminStatus'] == 'up' && $sdp['sdpOperStatus'] == 'down') {
            $operstate_status_color = 'danger';
        }

        $host = @dbFetchRow('SELECT * FROM `ipv4_addresses` AS A, `ports` AS I, `devices` AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id', [$sdp['sdpFarEndInetAddress']]);
        $destination = $sdp['sdpFarEndInetAddress'];
        if (is_array($host)) {
            $destination = generate_device_link($host, 0, array('tab' => 'routing', 'proto' => 'mpls'));
        }
        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'mpls')) . '</td>
            <td>' . $sdp['sdp_oid'] . '</td>
            <td>' . $destination . '</td>
            <td>' . $sdp['sdpDelivery'] . '</td>
            <td>' . $sdp['sdpActiveLspType'] . '</td>
            <td>' . $sdp['sdpDescription'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $sdp['sdpAdminStatus'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $sdp['sdpOperStatus'] . '</td>
            <td>' . $sdp['sdpAdminPathMtu'] . '</td>
            <td>' . $sdp['sdpOperPathMtu'] . '</td>
            <td>' . formatUptime($sdp['sdpLastMgmtChange']) . '</td>
            <td>' . formatUptime($sdp['sdpLastStatusChange']) . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end sdps view

if ($vars['view'] == 'sdpbinds') {
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="SDP Binding identifier. SDP identifier : Service identifier">SDP Bind Id</a></th>
        <th><a title="This object specifies whether this Service SDP binding is a spoke or a mesh.">Bind Type</a></th>
        <th><a title="The value of VC Type is an enumerated integer that specifies the type of virtual circuit (VC) associated with the SDP binding">VC Type</a></th>
        <th><a title="The desired state of this Service-SDP binding.">Admin State</a></th>
        <th><a title="The value of sdpBindOperStatus indicates the operating status of this Service-SDP binding.
up: The Service-SDP binding is operational.
noEgressLabel: The ingress label is available but the egress one is missing.
noIngressLabel:The egress label is available but the ingress one is not.
noLabels: Both the ingress and the egress labels are missing.
down: The binding is administratively down.
svcMtuMismatch: Both labels are available, but a service  MTU mismatch was detected between the local and the far-end devices.
sdpPathMtuTooSmall: The operating path MTU of the corresponding SDP minus the size of the SDP Bind control word (if applicable) is smaller than the service MTU.
sdpNotReady: The SDPs signaling session is down.
sdpDown: The SDP is not operationally up.
sapDown: The SAP associated with the service is down.">Oper State</a></th>
        <th><a title="The value of sysUpTime at the time of the most recent management-initiated change to this Service-SDP binding.">Last Mgmt Change at</a></th>
        <th><a title="The value of the object sdpBindLastStatusChange indicates the value of sysUpTime at the time of the most recent operating status change to this SDP Bind.">Last Status Change at</a></th>
        <th><a title="SDP Bind ingress forwarded packets">Ing Fwd Packets</a></th>
        <th><a title="SDP Bind ingress forwarded octets">Ing Fwd Octets</a></th>
        <th><a title="SDP Bind egress forwarded packets">Egr Fwd Packets</a></th>
        <th><a title="SDP Bind egress forwarded octets">Egr Fwd Octets</a></th>
        </tr>';

    $i = 0;

    foreach (dbFetchRows('SELECT * FROM `mpls_sdp_binds` ORDER BY `sdp_oid', `svc_oid`) as $sdpbind) {
        $device = device_by_id_cache($sdpbind['device_id']);
        if (!is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        $adminstate_status_color = $operstate_status_color = 'default';
        $failcode_status_color = 'warning';

        if ($sdpbind['sdpBindAdminStatus'] == 'up') {
            $adminstate_status_color = 'success';
        }
        if ($sdpbind['sdpBindOperStatus'] == 'up') {
            $operstate_status_color = 'success';
        } else {
            $operstate_status_color = 'danger';
        }

        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'mpls')) . '</td>
            <td>' . $sdpbind['sdp_oid'] . ':' . $sdpbind['svc_oid'] . '</td>
            <td>' . $sdpbind['sdpBindType'] . '</td>
            <td>' . $sdpbind['sdpBindVcType'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $sdpbind['sdpBindAdminStatus'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $sdpbind['sdpBindOperStatus'] . '</td>
            <td>' . formatUptime($sdpbind['sdpBindLastMgmtChange']) . '</td>
            <td>' . formatUptime($sdpbind['sdpBindLastStatusChange']) . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsIngFwdPackets'] . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsIngFwdOctets'] . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsEgrFwdPackets'] . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsEgrFwdOctets'] . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end sdpbinds view
