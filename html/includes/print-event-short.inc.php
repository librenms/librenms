<?php

if ($bg == $list_colour_a) {
    $bg = $list_colour_b;
} else {
    $bg = $list_colour_a;
}

unset($icon);
$icon_returned = geteventicon($entry['message']);
$icon_type = $icon_returned['icon'];
$icon_colour = $icon_returned['colour'];

if ($icon_type) {
    $icon = "<i class='fa $icon_type fa-lg' style='color:$icon_colour' aria-hidden='true'></i>";
} else {
    $icon = "<i class='fa fa-bookmark-o fa-lg' style='color:black' aria-hidden='true'></i>";
}

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
