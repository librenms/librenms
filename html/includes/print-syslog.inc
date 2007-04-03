
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  $hostname = gethostbyid($entry['host']);
  $interface = fixifname(getifbyid($entry['interface']));

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='/images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=150>
      $entry[datetime]
    </td>
    <td class=list-bold width=100>
      $entry[priority]
    </td>
    <td class=syslog width=100>
      $entry[program]
    </td>
    <td class=syslog>
      $entry[msg]
    </td>
    <td></td>
  </tr>");




?>
