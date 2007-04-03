
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  $hostname = gethostbyid($entry['host']);
  $interface = fixifname(getifbyid($entry['interface']));

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='/images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=180>
      $entry[humandate]
    </td>");
  if(!$_GET[id] && !$overview) {
    $dev['id'] = $entry['host'];
    $dev['hostname'] = $hostname;
    echo("<td class=list-bold width=250>
      " . generatedevicelink($dev) . "
    </td>");
  }

    $if['id'] = $entry[interface];
    $if['if'] = $interface;
    echo("<td class=list-bold width=175>
      " . generateiflink($if) . "
    </td>
    <td width=20>$icon</td>
    <td class=syslog>
      $entry[message]
    </td>
    <td></td>
  </tr>");




?>
