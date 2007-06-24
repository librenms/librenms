<?

  echo("<div style='font-family: courier, serif; margin: 10px';><strong>" . $device['sysDescr'] . "</strong></div>");

  $uptime = mysql_result(mysql_query("SELECT `attrib_value` FROM `devices_attribs` WHERE `device_id` = '" . $device['device_id'] . "' AND `attrib_type` = 'uptime'"), 0);

#  if(strtolower($device['os']) == "ios") {
    echo("
      <table width=100%>
        <tr>
          <td class=list-bold>Operating System</td>
          <td>" . $device['os'] . " " . $device['version'] . " ( " . $device['features'] . " )</td>
        </tr>
        <tr>
          <td class=list-bold>Hardware</td>
          <td>" . $device['hardware']. "</td>
        </tr>
        <tr>
          <td class=list-bold>Uptime</td>
          <td>" . formatUptime($uptime) . "</td>
        </tr>
        

      </table>");
  
#  }

?>
