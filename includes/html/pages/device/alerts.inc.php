<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

if (! isset($vars['section'])) {
    $vars['section'] = 'alerts';
}

echo '<br>';
echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<strong>Alerts</strong>  &#187; ';

if ($vars['section'] == 'alerts') {
    echo '<span class="pagemenu-selected">';
}
echo generate_link('Active alerts', $vars, ['section' => 'alerts']);
if ($vars['section'] == 'alerts') {
    echo '</span>';
}

echo ' | ';

if ($vars['section'] == 'alert-log') {
    echo '<span class="pagemenu-selected">';
}
echo generate_link('Alert history', $vars, ['section' => 'alert-log']);
if ($vars['section'] == 'alert-log') {
    echo '</span>';
}

echo '</div><br>';
echo '<div style="width:99%;margin:0 auto;">';

switch ($vars['section']) {
    case 'alerts':
        include 'includes/html/modal/alert_details.php';
        include 'includes/html/modal/alert_notes.inc.php';
        include 'includes/html/modal/alert_ack.inc.php';
        include 'includes/html/common/alerts.inc.php';
        echo implode('', $common_output);
        break;
    case 'alert-log':
        $vars['fromdevice'] = true;
        $device_id = (int) $vars['device'];
        include 'includes/html/modal/alert_details.php';
        include 'includes/html/common/alert-log.inc.php';
        echo implode('', $common_output);
        break;

    default:
        echo '</div>';
        echo report_this('Unknown section ' . $vars['section']);
        break;
}

echo '</div>';
