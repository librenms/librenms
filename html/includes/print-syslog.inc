
<?php

if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }

  if(!$entry['processed']) { $entry = process_syslog($entry, 1); }
  if(!$entry['deleted']) {

  echo("<tr style=\"background-color: $bg\">
    <td width=0></td>");

  echo("<td class=syslog width=125>" . $entry['date'] . "</td>");

#  $entry['hostname'] = shorthost(gethostbyid($entry['host']), 20);

  if($_GET['page'] != "device") { 
    echo("<td width=160><strong>".generatedevicelink($entry)."</strong></td>"); 
  }

  echo("  <td class=syslog>
      <strong>" . $entry['program'] . " : </strong>
      " . htmlspecialchars($entry['msg']) . "
    </td>
    <td></td>
  </tr>");

 }

?>
