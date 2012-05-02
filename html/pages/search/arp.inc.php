<?php print_optionbar_start(28); ?>

<table cellpadding="0" cellspacing="0" class="devicetable" width="100%">
  <tr>
  <form method="post" action="">
    <td width="200" style="padding: 1px;">
      <select name="device_id" id="device_id">
      <option value="">All Devices</option>
<?php

// Select the devices only with ARP tables
foreach (dbFetchRows("SELECT D.device_id AS device_id, `hostname` FROM `ipv4_mac` AS M, `ports` AS P, `devices` AS D WHERE M.interface_id = P.interface_id AND P.device_id = D.device_id GROUP BY `device_id` ORDER BY `hostname`;") as $data)
{
  echo('<option value="'.$data['device_id'].'"');
  if ($data['device_id'] == $_POST['device_id']) { echo("selected"); }
  echo(">".$data['hostname']."</option>");
}
?>
      </select>
    </td>
    <td width="200" style="padding: 1px;">Search by
      <select name="searchby" id="searchby">
      <option value="mac" <?php if ($_POST['searchby'] != "ip") { echo("selected"); } ?> >MAC Address</option>
      <option value="ip" <?php if ($_POST['searchby'] == "ip") { echo("selected"); } ?> >IP Address</option>
      </select>
    </td>
    <td>
    </td>
    <td width=400>
     <input type="text" name="address" id="address" size=40 value="<?php echo($_POST['address']); ?>" />
     <input style="align:right;" type=submit class=submit value=Search></div>
    </td>
  </form>
  </tr>
</table>

<?php

print_optionbar_end();

echo('<table width="100%" cellspacing="0" cellpadding="5">');

$query = "SELECT * FROM `ipv4_mac` AS M, `ports` AS P, `devices` AS D WHERE M.interface_id = P.interface_id AND P.device_id = D.device_id ";
if (isset($_POST['searchby']) && $_POST['searchby'] == "ip")
{
  $query .= " AND `ipv4_address` LIKE ?";
  $param = array("%".trim($_POST['address'])."%");
} else {
  $query .= " AND `mac_address` LIKE ?";
  $param = array("%".str_replace(array(':', ' ', '-', '.', '0x'),'',mres($_POST['address']))."%");
}

if (is_numeric($_POST['device_id']))
{
  $query  .= " AND P.device_id = ?";
  $param[] = $_POST['device_id'];
}
$query .= " ORDER BY M.mac_address";

echo('<tr class="tablehead"><th>MAC Address</th><th>IP Address</th><th>Device</th><th>Interface</th><th>Remote device</th><th>Remote interface</th></tr>');
foreach (dbFetchRows($query, $param) as $entry)
{
  if (!$ignore)
  {
    //why are they here for?
    //$speed = humanspeed($entry['ifSpeed']);
    //$type = humanmedia($entry['ifType']);

    if ($entry['ifInErrors'] > 0 || $entry['ifOutErrors'] > 0)
    {
      $error_img = generate_port_link($entry,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
    } else { $error_img = ""; }

    $arp_host = dbFetchRow("SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.interface_id = A.interface_id AND D.device_id = I.device_id", array($entry['ipv4_address']));
    if ($arp_host) { $arp_name = generate_device_link($arp_host); } else { unset($arp_name); }
    if ($arp_host) { $arp_if = generate_port_link($arp_host); } else { unset($arp_if); }
    if ($arp_host['device_id'] == $entry['device_id']) { $arp_name = "Localhost"; }
    if ($arp_host['interface_id'] == $entry['interface_id']) { $arp_if = "Local port"; }

    echo('<tr class="search">
        <td width="160">' . formatMac($entry['mac_address']) . '</td>
        <td width="140">' . $entry['ipv4_address'] . '</td>
            <td width="200" class="list-bold">' . generate_device_link($entry) . '</td>
        <td class="list-bold">' . generate_port_link($entry, makeshortif(fixifname($entry['ifDescr']))) . ' ' . $error_img . '</td>
            <td width="200">'.$arp_name.'</td>
        <td class="list-bold">'.$arp_if.'</td>
            </tr>');
  }

  unset($ignore);
}

echo("</table>");

?>
