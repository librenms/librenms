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

function find_child($components, $parent, $level)
{
    global $vars;

    foreach ($components as $id => $array) {
        if ($array['qos-type'] == 3) {
            continue;
        }
        if (($array['parent'] == $components[$parent]['sp-obj']) && ($array['sp-id'] == $components[$parent]['sp-id'])) {
            echo '<ul>';
            echo '<li>';
            if ($array['qos-type'] == 1) {
                // Its a policy, we need to make it a link.
                $linkvars = array_merge($vars, ['policy' => $id]);
                unset($linkvars['class']);
                echo '<a href="' . \LibreNMS\Util\Url::generate($linkvars) . '">' . $array['label'] . '</a>';
            } elseif ($array['qos-type'] == 2) {
                // Its a class, we need to make it a link.
                echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['policy' => $parent, 'class' => $id]) . '">' . $array['label'] . '</a>';
            } else {
                // Unknown, no link
                echo $array['label'];
            }
            if (isset($array['match'])) {
                echo ' (' . $array['match'] . ')';
            }

            find_child($components, $id, $level + 1);

            echo '</li>';
            echo '</ul>';
        }
    }
}

if (! isset($vars['policy'])) {
    // not set, find the first parent and use it.
    foreach ($components as $id => $array) {
        if (($array['qos-type'] == 1) && ($array['ifindex'] == $port['ifIndex']) && ($array['parent'] == 0)) {
            // Found the first policy
            $vars['policy'] = $id;
            continue;
        }
    }
}
echo "\n\n";

// Display the ingress policy at the top of the page.
echo "<div class='col-md-6'><ul class='mktree' id='ingress'>";
echo '<div><strong><i class="fa fa-sign-in"></i>&nbsp;Ingress Policy:</strong></div>';
$found = false;
foreach ($components as $id => $array) {
    if (($array['qos-type'] == 1) && ($array['ifindex'] == $port['ifIndex']) && ($array['direction'] == 1) && ($array['parent'] == 0)) {
        echo "<li class='liOpen'>";
        echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['policy' => $id]) . '">' . $array['label'] . '</a>';
        find_child($components, $id, 1);
        echo '</li>';
        $found = true;
    }
}
if (! $found) {
    // No Ingress policies
    echo '<div><i>No Policies</i></div>';
}
echo '</ul></div>';

// Display the egress policy at the top of the page.
echo "<div class='col-md-6'><ul class='mktree' id='egress'>";
echo '<div><strong><i class="fa fa-sign-out"></i>&nbsp;Egress Policy:</strong></div>';
$found = false;
foreach ($components as $id => $array) {
    if (($array['qos-type'] == 1) && ($array['ifindex'] == $port['ifIndex']) && ($array['direction'] == 2) && ($array['parent'] == 0)) {
        echo "<li class='liOpen'>";
        echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['policy' => $id]) . '">' . $array['label'] . '</a>';
        find_child($components, $id, 1);
        echo '</li>';
        $found = true;
    }
}
if (! $found) {
    // No Egress policies
    echo '<div><i>No Policies</i></div>';
}
echo "</ul></div>\n\n";

// Let's make sure the policy we are trying to access actually exists.
foreach ($components as $id => $array) {
    if (($array['qos-type'] == 1) && ($array['ifindex'] == $port['ifIndex']) && ($id == $vars['policy'])) {
        // The policy exists.

        echo "<div class='col-md-12'>&nbsp;</div>\n\n";

        // Display each graph row.
        echo "<div class='col-md-12'>";

        echo "<div class='graphhead'>Traffic by CBQoS Class (pre policy) - " . $components[$vars['policy']]['label'] . '</div>';
        $graph_array['policy'] = $vars['policy'];
        if (isset($vars['class'])) {
            $graph_array['class'] = $vars['class'];
        }
        $graph_type = 'port_cbqos_prebits';
        include 'includes/html/print-interface-graphs.inc.php';

        echo "<div class='graphhead'>Traffic by CBQoS Class (post policy) - " . $components[$vars['policy']]['label'] . '</div>';
        $graph_array['policy'] = $vars['policy'];
        if (isset($vars['class'])) {
            $graph_array['class'] = $vars['class'];
        }
        $graph_type = 'port_cbqos_traffic';
        include 'includes/html/print-interface-graphs.inc.php';

        echo "<div class='graphhead'>Packets by CBQoS Class (pre policy) - " . $components[$vars['policy']]['label'] . '</div>';
        $graph_array['policy'] = $vars['policy'];
        if (isset($vars['class'])) {
            $graph_array['class'] = $vars['class'];
        }
        $graph_type = 'port_cbqos_prepkts';
        include 'includes/html/print-interface-graphs.inc.php';

        echo "<div class='graphhead'>QoS Drops by CBQoS Class - " . $components[$vars['policy']]['label'] . '</div>';
        $graph_array['policy'] = $vars['policy'];
        if (isset($vars['class'])) {
            $graph_array['class'] = $vars['class'];
        }
        $graph_type = 'port_cbqos_bufferdrops';
        include 'includes/html/print-interface-graphs.inc.php';

        echo "<div class='graphhead'>Packet Drops by CBQoS Class - " . $components[$vars['policy']]['label'] . '</div>';
        $graph_array['policy'] = $vars['policy'];
        if (isset($vars['class'])) {
            $graph_array['class'] = $vars['class'];
        }
        $graph_type = 'port_cbqos_droppkts';
        include 'includes/html/print-interface-graphs.inc.php';

        echo "<div class='graphhead'>Buffer Drops by CBQoS Class - " . $components[$vars['policy']]['label'] . '</div>';
        $graph_array['policy'] = $vars['policy'];
        if (isset($vars['class'])) {
            $graph_array['class'] = $vars['class'];
        }
        $graph_type = 'port_cbqos_qosdrops';
        include 'includes/html/print-interface-graphs.inc.php';
        echo "</div>\n\n";
    }
}
