
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  $hostname = gethostbyid($entry['host']);
  $interface = fixifname(getifbyid($entry['interface']));

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=160>
      " . $entry['datetime'] . "
    </td>");
  if(!$_GET[id] && !$overview) {
    $dev['device_id'] = $entry['host'];
    $dev['hostname'] = $hostname;
    echo("<td class=list-bold width=150>
      " . generatedevicelink($dev, shorthost($dev['hostname'])) . "
    </td>");
  }

  echo("<td class=syslog>");

  if($interface) {
    $if['interface_id'] = $entry['interface'];
    $if['ifDescr'] = $interface;
    echo(generateiflink($if));
  }  
  echo(" " . htmlspecialchars($entry['message']) . "
    </td>
  </tr>");




?>
