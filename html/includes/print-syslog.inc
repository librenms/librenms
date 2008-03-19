
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

#  if($entry['interface']) {
#    $interface = fixifname(getifbyid($entry['interface']));
#  }

  $entry['hostname'] = gethostbyid($entry['device_id']);

  unset($icon);

#  $icon = geteventicon($entry['message']);
#  if($icon) {$icon = "<img src='images/16/$icon'>"; }

  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>");

  echo("<td class=syslog width=150>" . $entry['date'] . "</td>");

  if($_GET['page'] == "syslog") { 
    echo("<td><strong>".generatedevicelink($entry['device_id'], $entry['hostname'])."</strong></td>"); 
  }


  echo("  <td class=syslog>
      <strong>" . $entry['program'] . " : </strong>
      " . htmlspecialchars($entry['msg']) . "
    </td>
    <td></td>
  </tr>");

?>
