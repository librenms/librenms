<?php print_optionbar_start(28); ?>

  <form method="post" action="" class="form-inline" role="form">
    <div class="form-group">
      <select name="device_id" id="device_id" class="form-control input-sm">
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
    </div>
    <div class="form-group">
      <select name="interface" id="interface" class="form-control input-sm">
        <option value="">All Interfaces</option>
        <option value="Loopback%" <?php if ($_POST['interface'] == "Loopback%") { echo("selected"); } ?> >Loopbacks</option>
        <option value="Vlan%" <?php if ($_POST['interface'] == "Vlan%") { echo("selected"); } ?> >VLANs</option>
      </select>
    </div>
    <div class="form-group">
      <input type="text" name="address" id="address" size=40 value="<?php echo($_POST['address']); ?>" class="form-control input-sm" placeholder="Mac Address"/>
    </div>
     <button type="submit" class="btn btn-default input-sm">Search</button>
  </form>

<?php

print_optionbar_end();

echo('<table class="table table-condensed">');

$query = "SELECT * FROM `ports` AS P, `devices` AS D WHERE P.device_id = D.device_id ";
$query .= "AND `ifPhysAddress` LIKE ?";
$param = array("%".str_replace(array(':', ' ', '-', '.', '0x'),'',mres($_POST['address']))."%");

if (is_numeric($_POST['device_id']))
{
  $query  .= " AND P.device_id = ?";
  $param[] = $_POST['device_id'];
}
if ($_POST['interface'])
{
  $query .= " AND P.ifDescr LIKE ?";
  $param[] = $_POST['interface'];
}
$query .= " ORDER BY P.ifPhysAddress";

echo('<tr class="tablehead"><th>Device</a></th><th>Interface</th><th>MAC Address</th><th>Description</th></tr>');
foreach (dbFetchRows($query, $param) as $entry)
{
  if (!$ignore)
  {
    $speed = humanspeed($entry['ifSpeed']);
    $type = humanmedia($entry['ifType']);

    if ($entry['in_errors'] > 0 || $entry['out_errors'] > 0)
    {
      $error_img = generate_port_link($entry,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
    } else { $error_img = ""; }

    if (port_permitted($entry['port_id']))
    {
      $interface = ifLabel ($interface, $interface);

      echo('<tr class="search">
          <td class="list-bold">' . generate_device_link($entry) . '</td>
          <td class="list-bold">' . generate_port_link($entry, makeshortif(fixifname($entry['ifDescr']))) . ' ' . $error_img . '</td>
          <td>' . formatMac($entry['ifPhysAddress']) . '</td>
          <td>' . $entry['ifAlias'] . "</td>
        </tr>\n");
    }
  }

  unset($ignore);
}

echo("</table>");

?>
