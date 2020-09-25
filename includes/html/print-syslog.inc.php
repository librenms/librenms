<?php

if (device_permitted($entry['device_id'])) {
    $syslog_output .= '<tr>';

    // Stop shortening hostname. Issue #61
    // $entry['hostname'] = shorthost($entry['hostname'], 20);
    if ($vars['page'] != 'device') {
        $syslog_output .= '<td>' . $entry['date'] . '</td>
                        <td><strong>' . generate_device_link($entry) . '</strong></td>
                        <td><strong>' . $entry['program'] . ' : </strong> ' . htmlspecialchars($entry['msg']) . '</td>';
    } else {
        $syslog_output .= '<td><i>' . $entry['date'] . '</i>&nbsp;&nbsp;&nbsp;<strong>' . $entry['program'] . '</strong>&nbsp;&nbsp;&nbsp;' . htmlspecialchars($entry['msg']) . '</td>';
    }

    $syslog_output .= '</tr>';
}
