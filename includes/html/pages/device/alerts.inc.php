<?php

$vars['section'] ??= 'alerts';

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
        $vars['device_id'] = (int) $vars['device'];
        include 'includes/html/modal/alert_details.php';
        include 'includes/html/common/alert-log.inc.php';
        echo implode('', $common_output);
        break;

    default:
        echo '</div>';
        echo 'Unknown section';
        break;
}

echo '</div>';
