<?php

if ($bg == $list_colour_a) {
    $bg = $list_colour_b;
} else {
    $bg = $list_colour_a;
}

unset($icon);
$severity_colour = eventlog_severity($entry['severity']);

$icon = "<i class='fa fa-bookmark fa-lg $severity_colour' aria-hidden='true'></i>";

echo '<tr">
  <td>'.$icon.'&nbsp;
    '.$entry['humandate'].'
  </td>
  <td>';

if ($entry['type'] == 'interface') {
    $entry['link'] = '<b>'.generate_port_link(getifbyid($entry['reference'])).'</b>';
}

  echo $entry['link'].' '.htmlspecialchars($entry['message']).'</td>
</tr>';
