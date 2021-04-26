<?php

print_optionbar_start();

$link_array = [
    'page'    => 'routing',
    'protocol'  => 'mpls',
];

if (! isset($vars['view'])) {
    $vars['view'] = 'lsp';
}

echo '<span style="font-weight: bold;">MPLS</span> &#187; ';

if ($vars['view'] == 'lsp') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('LSPs', $link_array, ['view' => 'lsp']);
if ($vars['view'] == 'lsp') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'paths') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Paths', $link_array, ['view' => 'paths']);
if ($vars['view'] == 'paths') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'sdps') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('SDPs', $link_array, ['view' => 'sdps']);
if ($vars['view'] == 'sdps') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'sdpbinds') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('SDP binds', $link_array, ['view' => 'sdpbinds']);
if ($vars['view'] == 'sdpbinds') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'services') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Services', $link_array, ['view' => 'services']);
if ($vars['view'] == 'services') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'saps') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('SAPs', $link_array, ['view' => 'saps']);
if ($vars['view'] == 'saps') {
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

        if (! is_integer($i / 2)) {
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
            $destination = generate_device_link($host, 0, ['tab' => 'routing', 'proto' => 'mpls']);
        }

        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'mpls']) . '</td>
            <td>' . $lsp['mplsLspName'] . '</td>
            <td>' . $destination . '</td>
            <td>' . $lsp['vrf_name'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $lsp['mplsLspAdminState'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $lsp['mplsLspOperState'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($lsp['mplsLspLastChange']) . '</td>
            <td>' . $lsp['mplsLspTransitions'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($lsp['mplsLspLastTransition']) . '</td>
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
        if (! is_integer($i / 2)) {
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
        $destination = $path['mplsLspPathFailNodeAddr'];
        if (is_array($host)) {
            $destination = generate_device_link($host, 0, ['tab' => 'routing', 'proto' => 'mpls']);
        }
        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'mpls', 'view' => 'paths']) . '</td>
            <td>' . $path['mplsLspName'] . '</td>
            <td>' . $path['path_oid'] . '</td>
            <td>' . $path['mplsLspPathType'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $path['mplsLspPathAdminState'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $path['mplsLspPathOperState'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($path['mplsLspPathLastChange']) . '</td>
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

    foreach (dbFetchRows('SELECT * FROM `mpls_sdps` ORDER BY `sdp_oid`') as $sdp) {
        $device = device_by_id_cache($sdp['device_id']);
        if (! is_integer($i / 2)) {
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
            $destination = generate_device_link($host, 0, ['tab' => 'routing', 'proto' => 'mpls']);
        }
        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'mpls', 'view' => 'sdps']) . '</td>
            <td>' . $sdp['sdp_oid'] . '</td>
            <td>' . $destination . '</td>
            <td>' . $sdp['sdpDelivery'] . '</td>
            <td>' . $sdp['sdpActiveLspType'] . '</td>
            <td>' . $sdp['sdpDescription'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $sdp['sdpAdminStatus'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $sdp['sdpOperStatus'] . '</td>
            <td>' . $sdp['sdpAdminPathMtu'] . '</td>
            <td>' . $sdp['sdpOperPathMtu'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($sdp['sdpLastMgmtChange']) . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($sdp['sdpLastStatusChange']) . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end sdps view

if ($vars['view'] == 'sdpbinds') {
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="The value of this object specifies the Service identifier. This value should be unique within the service domain">Service Id</a></th>
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

    foreach (dbFetchRows('SELECT b.*, s.svc_oid AS svcId FROM `mpls_sdp_binds` AS b LEFT JOIN `mpls_services` AS s ON `b`.`svc_id` = `s`.`svc_id` ORDER BY `sdp_oid`, `svc_oid`') as $sdpbind) {
        $device = device_by_id_cache($sdpbind['device_id']);
        if (! is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        $adminstate_status_color = $operstate_status_color = 'default';
        $failcode_status_color = 'warning';

        if ($sdpbind['sdpBindAdminStatus'] == 'up') {
            $adminstate_status_color = 'success';
        }
        if ($sdpbind['sdpBindAdminStatus'] == 'up' && $sdpbind['sdpBindOperStatus'] == 'up') {
            $operstate_status_color = 'success';
        } elseif ($sdpbind['sdpBindAdminStatus'] == 'up' && $sdpbind['sdpBindOperStatus'] == 'down') {
            $operstate_status_color = 'danger';
        }

        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'mpls', 'view' => 'sdpbinds']) . '</td>
            <td>' . $sdpbind['svcId'] . '</td>
            <td>' . $sdpbind['sdp_oid'] . ':' . $sdpbind['svc_oid'] . '</td>
            <td>' . $sdpbind['sdpBindType'] . '</td>
            <td>' . $sdpbind['sdpBindVcType'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $sdpbind['sdpBindAdminStatus'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $sdpbind['sdpBindOperStatus'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($sdpbind['sdpBindLastMgmtChange']) . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($sdpbind['sdpBindLastStatusChange']) . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsIngFwdPackets'] . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsIngFwdOctets'] . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsEgrFwdPackets'] . '</td>
            <td>' . $sdpbind['sdpBindBaseStatsEgrFwdOctets'] . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end sdpbinds view

if ($vars['view'] == 'services') {
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="The value of this object specifies the Service identifier. This value should be unique within the service domain.">Service Id</a></th>
        <th><a title="The value of this object specifies the service type: e.g. epipe, tls, etc.">Type</a></th>
        <th><a title="The value of this object specifies the ID of the customer who owns this service.">Customer</a></th>
        <th><a title="The value of this object specifies the desired state of this service.">Admin Status</a></th>
        <th><a title="The value of this object indicates the operating state of this service. The requirements for a service to be operationally up depend on the service type:
epipe, apipe, fpipe, ipipe and cpipe services are up when the service is administratively up and either both SAPs or a SAP and a spoke SDP Bind are operationally up.
tls services are up when the service is administratively up and either at least one SAP or spoke SDP Bind or one mesh SDP Bind is operationally up.
tls service that has vxlan configuration is up when the service is administratively up.
ies services are up when the service is administratively up and at least one interface is operationally up.
vprn services are up when the service is administratively up however routing functionality is available only when TIMETRA-VRTR-MIB::vRtrOperState is up.">Oper Status</a></th>
        <th><a title="The value of the object svcDescription specifiers an optional, generic information about this service.">Description</a></th>
        <th><a title="The value of the object svcMtu specifies the largest frame size (in octets) that this service can handle. Setting svcMtu to a value of zero (0), causes the agent to recalculate the default MTU size. The default value of this object depends on the service type: 1514 octets for epipe and tls, 1508 for apipe and fpipe, and 1500 octets for vprn, ipipe and ies, 1514 octets for cpipe.">Service MTU</a></th>
        <th><a title="The value of the object svcNumSaps indicates the number of SAPs defined on this service.">Num SAPs</a></th>
        <th><a title="The value of of the object svcLastMgmtChange indicates the value of sysUpTime at the time of the most recent management-initiated change to this service.">Last Mgmt Change at</a></th>
        <th><a title="The value of the object svcLastStatusChange indicates the value of sysUpTime at the time of the most recent operating status change to his service.">Last Status Change at</a></th>
        <th><a title="The value of this object specifies, for a IES or VPRN service the associated virtual router instance where its interfaces are defined. This object has a special significance for the VPRN service as it can be used to associate the service to a specific virtual router instance. If no routing instance is specified or a value of zero (0) is specified, the agent will assign the vRtrID index value that would have been returned by the vRtrNextVRtrID object in the TIMETRA-VRTR-MIB">VRF</a></th>
        <th><a title="The value specifies whether the MAC learning process is enabled in this TLS.">MAC Learning</a></th>
        <th><a title="The value of the object svcTlsFdbTableSize specifies the maximum number of learned and static entries allowed in the FDB of this service. The maximum value of svcTlsFdbTableSize depends on the platform/chassis mode.">FDB Table Size</a></th>
        <th><a title="The value of the object svcTlsFdbNumEntries indicates the current number of entries allocated in the FDB of this service.">FDB Entries</a></th>
        <th><a title="The value of the object svcTlsStpAdminStatus specifies the administrative state of the Spanning Tree Protocol instance associated with this service.">STP Admin Status</a></th>
        <th><a title="The value of the object svcTlsStpOperStatus indicates the operating status of the Spanning Tree Protocol instance associated with this service.">STP Oper Status</a></th>
        </tr>';

    $i = 0;

    foreach (dbFetchRows('SELECT s.*, v.vrf_name FROM `mpls_services` AS s LEFT JOIN  `vrfs` AS v ON `s`.`svcVRouterId` = `v`.`vrf_oid` AND `s`.`device_id` = `v`.`device_id` ORDER BY `svc_oid`') as $svc) {
        $device = device_by_id_cache($svc['device_id']);
        if (! is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        $adminstate_status_color = $operstate_status_color = 'default';
        $failcode_status_color = 'warning';

        if ($svc['svcAdminStatus'] == 'up') {
            $adminstate_status_color = 'success';
        }
        if ($svc['svcAdminStatus'] == 'up' && $svc['svcOperStatus'] == 'up') {
            $operstate_status_color = 'success';
        } elseif ($svc['svcAdminStatus'] == 'up' && $svc['svcOperStatus'] == 'down') {
            $operstate_status_color = 'danger';
        }

        $fdb_usage_perc = $svc['svcTlsFdbNumEntries'] / $svc['svcTlsFdbTableSize'] * 100;
        if ($fdb_usage_perc > 95) {
            $fdb_status_color = 'danger';
        } elseif ($fdb_usage_perc > 75) {
            $fdb_status_color = 'warning';
        } else {
            $fdb_status_color = 'success';
        }

        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'mpls', 'view' => 'services']) . '</td>
            <td>' . $svc['svc_oid'] . '</td>
            <td>' . $svc['svcType'] . '</td>
            <td>' . $svc['svcCustId'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $svc['svcAdminStatus'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $svc['svcOperStatus'] . '</td>
            <td>' . $svc['svcDescription'] . '</td>
            <td>' . $svc['svcMtu'] . '</td>
            <td>' . $svc['svcNumSaps'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($svc['svcLastMgmtChange']) . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($svc['svcLastStatusChange']) . '</td>
            <td>' . $svc['vrf_name'] . '</td>
            <td>' . $svc['svcTlsMacLearning'] . '</td>
            <td>' . $svc['svcTlsFdbTableSize'] . '</td>
            <td><span class="label label-' . $fdb_status_color . '">' . $svc['svcTlsFdbNumEntries'] . '</td>
            <td>' . $svc['svcTlsStpAdminStatus'] . '</td>
            <td>' . $svc['svcTlsStpOperStatus'] . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end services view

if ($vars['view'] == 'saps') {
    echo '<tr><th><a title="Device">Device</a></th>
        <th><a title="The value of this object specifies the Service identifier.">Service Id</a></th>
        <th><a title="The ID of the access port where this SAP is defined.">SAP Port</a></th>
        <th><a title="The value of the label used to identify this SAP on the access port specified by sapPortId.">Encapsulation</a></th>
        <th><a title="This object indicates the type of service where this SAP is defined.">Type</a></th>
        <th><a title="Generic information about this SAP.">Description</a></th>
        <th><a title="The desired state of this SAP.">Admin Status</a></th>
        <th><a title="The value of the object sapOperStatus indicates the operating state of this SAP.">Oper Satatus</a></th>
        <th><a title="The value of sysUpTime at the time of the most recent management-initiated change to this SAP.">Last Mgmt Change at</a></th>
        <th><a title="The value of sysUpTime at the time of the most recent operating status change to this SAP.">Last Oper Change at</a></th>
        </tr>';

    $i = 0;

    foreach (dbFetchRows('SELECT * FROM `mpls_saps` ORDER BY `device_id`, `svc_oid`, `sapPortId`, `sapEncapValue`') as $sap) {
        $port = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifName` = ?', [$sap['device_id'], $sap['ifName']]);
        $port = cleanPort($port);

        $device = device_by_id_cache($sap['device_id']);
        if (! is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        $adminstate_status_color = $operstate_status_color = 'default';
        $failcode_status_color = 'warning';

        if ($sap['sapAdminStatus'] == 'up') {
            $adminstate_status_color = 'success';
        }
        if ($sap['sapAdminStatus'] == 'up' && $sap['sapOperStatus'] == 'up') {
            $operstate_status_color = 'success';
        } elseif ($sap['sapAdminStatus'] == 'up' && $sap['sapOperStatus'] == 'down') {
            $operstate_status_color = 'danger';
        }

        echo "<tr bgcolor=$bg_colour>
            <td>" . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'mpls', 'view' => 'saps']) . '</td>
            <td>' . generate_sap_url($sap, $sap['svc_oid']) . '</td>
            <td>' . generate_port_link($port) . '</td>
            <td>' . $sap['sapEncapValue'] . '</td>
            <td>' . $sap['sapType'] . '</td>
            <td>' . $sap['sapDescription'] . '</td>
            <td><span class="label label-' . $adminstate_status_color . '">' . $sap['sapAdminStatus'] . '</td>
            <td><span class="label label-' . $operstate_status_color . '">' . $sap['sapOperStatus'] . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($sap['sapLastMgmtChange']) . '</td>
            <td>' . \LibreNMS\Util\Time::formatInterval($sap['sapLastStatusChange']) . '</td>';
        echo '</tr>';

        $i++;
    }
    echo '</table></div>';
} // end sap view
