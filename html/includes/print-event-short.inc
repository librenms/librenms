
<?

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  $hostname = gethostbyid($entry['host']);
  $interface = fixifname(getifbyid($entry['interface']));

  unset($icon);

  $icon = geteventicon($entry['message']);
  if($icon) {$icon = "<img src='/images/16/$icon'>"; }


  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>
    <td class=syslog width=140>
      $entry[humandate]
    </td>");
    $if['id'] = $entry[interface];
    $if['if'] = makeshortif($interface);
    echo("<td class=list-bold width=75>
      " . generateiflink($if) . "
    </td>
    <td class=syslog>" . 
      truncate($entry[message], 45)
    . "</td>
    <td></td>
  </tr>");




?>
