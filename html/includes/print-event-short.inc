
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  $hostname = gethostbyid($entry['host']);
  $interface = fixifname(getifbyid($entry['interface']));

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='/images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=130>
      $entry[humandate]
    </td>");
    $if['id'] = $entry[interface];
    $if['if'] = $interface;
    echo("<td class=list-bold>
      " . generateiflink($if) . "
    </td>
    <td width=30 align=center>$icon</td>
    <td class=syslog>
      $entry[message]
    </td>
    <td></td>
  </tr>");




?>
