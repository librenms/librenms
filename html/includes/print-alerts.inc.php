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

if ($alert_state != '') {
    if ($alert_state == '0') {
        $glyph_icon  = 'ok';
        $glyph_color = 'green';
        $text        = 'Ok';
    } elseif ($alert_state == '1') {
        $glyph_icon  = 'remove';
        $glyph_color = 'red';
        $text        = 'Alert';
    } elseif ($alert_state == '2') {
        $glyph_icon  = 'info-sign';
        $glyph_color = 'lightgrey';
        $text        = 'Ack';
    } elseif ($alert_state == '3') {
        $glyph_icon  = 'arrow-down';
        $glyph_color = 'orange';
        $text        = 'Worse';
    } elseif ($alert_state == '4') {
        $glyph_icon  = 'arrow-up';
        $glyph_color = 'khaki';
        $text        = 'Better';
    }//end if
    echo "<td><b><span class='glyphicon glyphicon-".$glyph_icon."' style='color:".$glyph_color."'></span> $text</b></td>";
}//end if

echo '</tr>';
