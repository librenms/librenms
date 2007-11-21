
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

#  if($entry['interface']) {
#    $interface = fixifname(getifbyid($entry['interface']));
#  }

  $entry['device_id'] = $entry['host'];
  $entry['hostname'] = gethostbyid($entry['host']);

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='/images/16/$icon'>"; }



  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=175>");

  if($page == "syslog") {
    echo("" . generatedevicelink($entry) . "<br />");
  }

  echo($entry['datetime'] . "
    </td>
    <td class=syslog width=200>
      " . $entry['program'] . "
    </td> 
    <td class=syslog>
      " . htmlspecialchars($entry['msg']) . "
    </td>
    <td></td>
  </tr>");




?>
