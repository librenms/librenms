<?php
/*
 * LibreNMS module to display Cisco Class-Based QoS Details
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

function find_child($COMPONENTS,$parent,$level) {
    global $vars;

    foreach($COMPONENTS as $ID => $ARRAY) {
        if ($ARRAY['qos-type'] == 3) {
            continue;
        }
        if (($ARRAY['parent'] == $COMPONENTS[$parent]['sp-obj']) && ($ARRAY['sp-id'] == $COMPONENTS[$parent]['sp-id'])) {
            echo "<ul>";
            echo "<li>";
            if ($ARRAY['qos-type'] == 1) {
                // Its a policy, we need to make it a link.
                echo('<a href="' . generate_url($vars, array('policy' => $ID)) . '">' . $ARRAY['label'] . '</a>');
            }
            else {
                // No policy, no link
                echo $ARRAY['label'];
            }
            if (isset($ARRAY['match'])) {
                echo ' ('.$ARRAY['match'].')';
            }

            find_child($COMPONENTS,$ID,$level+1);

            echo "</li>";
            echo "</ul>";
        }
    }
}

$rrdarr = glob($config['rrd_dir'].'/'.$device['hostname'].'/port-'.$port['ifIndex'].'-cbqos-*.rrd');
if (!empty($rrdarr)) {
    require_once "../includes/component.php";
    $COMPONENT = new component();
    $options['filter']['type'] = array('=','Cisco-CBQOS');
    $COMPONENTS = $COMPONENT->getComponents($device['device_id'],$options);

    // We only care about our device id.
    $COMPONENTS = $COMPONENTS[$device['device_id']];

    if (isset($vars['policy'])) {
        // if a policy is set try to use it.
        $graph_array['policy'] = $vars['policy'];
    }
    else {
        // if not, find the first parent and use it.
        foreach ($COMPONENTS as $ID => $ARRAY) {
            if ( ($ARRAY['qos-type'] == 1) && ($ARRAY['ifindex'] == $port['ifIndex'])  && ($ARRAY['parent'] == 0) ) {
                // Found the first policy
                $graph_array['policy'] = $ID;
                continue;
            }
        }
    }
    echo "\n\n";

    // Display the ingress policy at the top of the page.
    echo "<div class='col-md-6'><ul class='mktree' id='ingress'>";
    echo '<div><strong><i class="fa fa-sign-in"></i>&nbsp;Ingress Policy:</strong></div>';
    $FOUND = false;
    foreach ($COMPONENTS as $ID => $ARRAY) {
        if ( ($ARRAY['qos-type'] == 1) && ($ARRAY['ifindex'] == $port['ifIndex']) && ($ARRAY['direction'] == 1)  && ($ARRAY['parent'] == 0) ) {
            echo "<li class='liOpen'>";
            echo('<a href="' . generate_url($vars, array('policy' => $ID)) . '">' . $ARRAY['label'] . '</a>');
            find_child($COMPONENTS,$ID,1);
            echo "</li>";
            $FOUND = true;
        }
    }
    if (!$FOUND) {
        // No Ingress policies
        echo '<div><i>No Policies</i></div>';
    }
    echo '</ul></div>';

    // Display the egress policy at the top of the page.
    echo "<div class='col-md-6'><ul class='mktree' id='egress'>";
    echo '<div><strong><i class="fa fa-sign-out"></i>&nbsp;Egress Policy:</strong></div>';
    $FOUND = false;
    foreach ($COMPONENTS as $ID => $ARRAY) {
        if ( ($ARRAY['qos-type'] == 1) && ($ARRAY['ifindex'] == $port['ifIndex']) && ($ARRAY['direction'] == 2)  && ($ARRAY['parent'] == 0) ) {
            echo "<li class='liOpen'>";
            echo('<a href="' . generate_url($vars, array('policy' => $ID)) . '">' . $ARRAY['label'] . '</a>');
            find_child($COMPONENTS,$ID,1);
            echo "</li>";
            $FOUND = true;
        }
    }
    if (!$FOUND) {
        // No Egress policies
        echo '<div><i>No Policies</i></div>';
    }
    echo "</ul></div>\n\n";

    // Let's make sure the policy we are trying to access actually exists.
    foreach ($COMPONENTS as $ID => $ARRAY) {
        if ( ($ARRAY['qos-type'] == 1) && ($ARRAY['ifindex'] == $port['ifIndex']) && ($ID == $graph_array['policy']) ) {
            // The policy exists.

            echo "<div class='col-md-12'>&nbsp;</div>\n\n";

            // Display each graph row.
            echo "<div class='col-md-12'>";
            echo "<div class='graphhead'>Traffic by CBQoS Class - ".$COMPONENTS[$graph_array['policy']]['label']."</div>";
            $graph_type = 'port_cbqos_traffic';
            include 'includes/print-interface-graphs.inc.php';

            echo "<div class='graphhead'>QoS Drops by CBQoS Class - ".$COMPONENTS[$graph_array['policy']]['label']."</div>";
            $graph_type = 'port_cbqos_bufferdrops';
            include 'includes/print-interface-graphs.inc.php';

            echo "<div class='graphhead'>Buffer Drops by CBQoS Class - ".$COMPONENTS[$graph_array['policy']]['label']."</div>";
            $graph_type = 'port_cbqos_qosdrops';
            include 'includes/print-interface-graphs.inc.php';
            echo "</div>\n\n";
        }
    }
}
