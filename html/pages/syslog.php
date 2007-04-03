<meta http-equiv="refresh" content="60">
<?

$sql = "select *,DATE_FORMAT(datetime, '%D %M %Y %T') as humandate from syslog ORDER BY datetime DESC LIMIT 1000";

echo("
<table cellspacing=0 cellpadding=2>
  <tr class=interface align=center>
    <td width=4>
    </td>
    <td>
      Time
    </td>
    <td width=5>
    </td>
    <td>
      Hostname
    </td>
    <td width=5>
    </td>
    <td>
      Type
    </td>
    <td width=5>
    </td>
    <td>
      Message
    </td>
    <td width=10>
    </td>
  </tr>");

$query = mysql_query($sql);
while($event = mysql_fetch_array($query)) 
{
  unset($class);
  unset($argh);

  $event[msg] = preg_replace("/.*%/", "", $event[msg]);
  $event[msg] = preg_replace("/[0-9]+:\ /", "", $event[msg]);

  $prefix = preg_replace ("/(.+):\ .*/", "\\1", $event[msg]);

  $event[msg] = preg_replace ("/.+:\ /", "", $event[msg]);

  if($prefix == $event[msg]) { unset ($prefix); }

  $prefix = str_replace("CRYPTO-4-RECVD_PKT_INV_SPI: decaps", "Crypto Invalid SPI", $prefix);
  $prefix = str_replace("LINEPROTO-5-UPDOWN", "Lineproto Up/Down", $prefix);
  $prefix = str_replace("LINK-3-UPDOWN", "Link Up/Down", $prefix);
  $prefix = str_replace("LINEPROTO-SP-5-UPDOWN", "Lineproto Up/Down", $prefix);
  $prefix = str_replace("LINK-SP-3-UPDOWN", "Link Up/Down", $prefix);

  $prefix = str_replace("PIM-6-INVALID_RP_JOIN", "PIM Invalid RP Join", $prefix);
  $prefix = str_replace("BGP-3-NOTIFICATION", "BGP Notification", $prefix);
  $prefix = str_replace("LINK-3-UPDOWN", "Link Up/Down", $prefix);
  $prefix = str_replace("DIALER-6-UNBIND", "Dialer Unbound", $prefix);
  $prefix = str_replace("DIALER-6-BIND", "Dialer Bound", $prefix);
  $prefix = str_replace("SYS-5-CONFIG_I", "System Configured", $prefix);
  $prefix = str_replace("VPDN-6-CLOSED", "VPDN Closed", $prefix);
  $prefix = str_replace("DIALER-6-BIND", "Dialer Bound", $prefix);
  $prefix = str_replace("PCMCIAFS-5-DIBERR", "PCMCIA FS Error", $prefix);
  $prefix = str_replace("BGP-5-ADJCHANGE", "BGP Adj Change", $prefix);
  $prefix = str_replace("MSDP-5-PEER_UPDOWN", "MSDP Peer UP/Down", $prefix);
  $prefix = str_replace("SYS-5-CONFIG_I", "System Configured", $prefix);

  $prefix = preg_replace("/.*ETHER-3-UNDERFLO/", "Ethernet Underflow", $prefix);

  if(strstr($event[msg], "BGP authentication failure") !== false) { $class = "pinkbg"; }
  if(strstr($event[msg], "Down BGP Notification received") !== false) { $class = "redbg"; }
  if(strstr($event[msg], "DOWN on interface") !== false) { $class = "redbg"; }
  if(strstr($event[msg], "from FULL to DOWN") !== false) { $class = "redbg"; }
  if(strstr($event[msg], "changed state to down") !== false) { $class = "redbg"; }
  if(strstr($event[msg], "(cease)") !== false) { $class = "redbg"; }
  if(strstr($event[msg], "(hold time expired)") !== false) { $class = "redbg"; }
  if(strstr($event[msg], "Configured from console") !== false) { $class = "bluebg"; }
  if(strstr($event[msg], "DR change ") !== false) { $class = "bluebg"; }
  if(strstr($event[msg], "Up") !== false) { $class = "greenbg"; }
  if(strstr($event[msg], "from LOADING to FULL") !== false) { $class = "greenbg"; }
  if(strstr($event[msg], "UP on interface ") !== false) { $class = "greenbg"; }  
  if(strstr($event[msg], "changed state to up") !== false) { $class = "greenbg"; }
  if(strstr($event[msg], "A format in this router is required") !== false) { $class = "greybg"; }
  if(strstr($event[msg], "bytes failed from") !== false) { $class = "greybg"; }
  if($event[msg] == "Attempted to connect to RSHELL from 195.74.96.24" ) { $argh = 1; }

  $event[msg] = str_replace("PCMCIA disk 0 is formatted from a different router or PC. A format in this router is required before an image can be booted from this device", "PCMCIA diak 0 is incorrectly formatted", $event[msg]);

if(!$argh) {
   echo ("
  <tr class='$class'>
<td width=4>
    </td>
    <td class=syslog>
      $event[humandate]     
    </td>
    <td width=5>
    </td>
    <td class=syslog>
      $event[host]
    </td>
    <td width=5>
    </td>
    <td class=syslog>
      $prefix
    </td>
    <td width=5>
    </td>
    <td class=syslog>
      $event[msg]
    </td>
<td width=4>
    </td>
  </tr>

");
}

}

?>
</table>
