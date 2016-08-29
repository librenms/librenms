<?php

if ($bg == $list_colour_a) {
    $bg = $list_colour_b;
} else {
    $bg = $list_colour_a;
}

unset($icon);

$icon = geteventicon($entry['message']);
if ($icon) {
    $icon = "<img src='images/16/$icon'>";
}

echo '<tr">
  <td></td>
  <td>
    '.$entry['humandate'].'
  </td>
  <td>';

if ($entry['type'] == 'interface') {
    $entry['link'] = '<b>'.generate_port_link(getifbyid($entry['reference'])).'</b>';
}

  echo $entry['link'].' '.htmlspecialchars($entry['message']).'</td>
  <td></td>
</tr>';
