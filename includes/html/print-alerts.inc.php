<?php

$hostname    = gethostbyid($alert_entry['device_id']);
$alert_state = $alert_entry['state'];

echo '<tr>
    <td>
    '.$alert_entry['time_logged'].'
    </td>';

if (!isset($alert_entry['device'])) {
    $dev = device_by_id_cache($alert_entry['device_id']);
    echo '<td>
        '.generate_device_link($dev, shorthost($dev['hostname'])).'
        </td>';
}

echo '<td>'.htmlspecialchars($alert_entry['name']).'</td>';

echo "<td>";
if ($alert_state != '') {
    if ($alert_state == '0') {
        $fa_icon  = 'check';
        $fa_color = 'success';
        $text     = 'Ok';
    } elseif ($alert_state == '1') {
        $fa_icon  = 'remove';
        $fa_color = 'danger';
        $text     = 'Alert';
    } elseif ($alert_state == '2') {
        $fa_icon  = 'info-circle';
        $fa_color = 'muted';
        $text     = 'Ack';
    } elseif ($alert_state == '3') {
        $fa_icon  = 'arrow-down';
        $fa_color = 'warning';
        $text     = 'Worse';
    } elseif ($alert_state == '4') {
        $fa_icon  = 'arrow-up';
        $fa_color = 'info';
        $text     = 'Better';
    }//end if
    echo "<b><i class='fa fa-fw fa-".$fa_icon." text-".$fa_color."'></i> $text</b>";
}
echo "</td>";

echo '</tr>';
