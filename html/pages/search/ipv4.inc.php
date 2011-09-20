<?php print_optionbar_start(28); ?>

<table cellpadding="0" cellspacing="0" class="devicetable" width="100%">
  <tr>
  <form method="post" action="">
    <td width="200" style="padding: 1px;">
      <select name="device_id" id="device_id">
      <option value="">All Devices</option>
<?php
foreach (dbFetchRows("SELECT `device_id`,`hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`") as $data)
{
  echo('<option value="'.$data['device_id'].'"');
  if ($data['device_id'] == $_POST['device_id']) { echo("selected"); }
  echo(">".$data['hostname']."</option>");
}
?>
      </select>
    </td>
    <td width="200" style="padding: 1px;">
      <select name="interface" id="interface">
      <option value="">All Interfaces</option>
      <option value="Loopback%" <?php if ($_POST['interface'] == "Loopback%") { echo("selected"); } ?> >Loopbacks</option>
      <option value="Vlan%" <?php if ($_POST['interface'] == "Vlan%") { echo("selected"); } ?> >VLANs</option>
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

$query = "SELECT * FROM `ipv4_addresses` AS A, `ports` AS I, `devices` AS D, `ipv4_networks` AS N WHERE I.interface_id = A.interface_id AND I.device_id = D.device_id AND N.ipv4_network_id = A.ipv4_network_id ";

if (is_numeric($_POST['device_id']))
{
  $query  .= " AND I.device_id = ?";
  $param[] = $_POST['device_id'];
}
if ($_POST['interface'])
{
  $query .= " AND I.ifDescr LIKE ?";
  $param[] = $_POST['interface'];
}
$query .= " ORDER BY A.ipv4_address";

echo('<tr class="tablehead"><th>Device</a></th><th>Interface</th><th>Address</th><th>Description</th></tr>');

$row = 1;

foreach (dbFetchRows($query, $param) as $interface)
{
  if ($_POST['address'])
  {
    list($addy, $mask) = explode("/", $_POST['address']);
    if (!$mask) { $mask = "32"; }
    if (!match_network($addy . "/" . $mask, $interface['ipv4_address'])) { $ignore = 1; }
  }

  if (!$ignore)
  {
    if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $speed = humanspeed($interface['ifSpeed']);
    $type = humanmedia($interface['ifType']);

    list($prefix, $length) = explode("/", $interface['ipv4_network']);

    if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0)
    {
      $error_img = generate_port_link($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
    } else { $error_img = ""; }

    if (port_permitted($interface['interface_id']))
    {
      $interface = ifLabel ($interface, $interface);

      echo('<tr bgcolor="' . $row_colour . '">
          <td class="list-bold">' . generate_device_link($interface) . '</td>
          <td class="list-bold">' . generate_port_link($interface) . ' ' . $error_img . '</td>
          <td>' . $interface['ipv4_address'] . '/'.$length.'</td>
          <td>' . $interface['ifAlias'] . "</td>
        </tr>\n");

      $row++;
    }
  }

  unset($ignore);
}

echo("</table>");

?>
