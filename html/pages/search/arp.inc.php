<?php print_optionbar_start(28); ?>

  <form method="post" action="" class="form-inline" role="form">
    <div class="form-group">
      <select name="device_id" id="device_id" class="form-control input-sm">
        <option value="">All Devices</option>
<?php

// Select the devices only with ARP tables
foreach (dbFetchRows("SELECT D.device_id AS device_id, `hostname` FROM `ipv4_mac` AS M, `ports` AS P, `devices` AS D WHERE M.port_id = P.port_id AND P.device_id = D.device_id GROUP BY `device_id` ORDER BY `hostname`;") as $data)
{
  echo('<option value="'.$data['device_id'].'"');
  if ($data['device_id'] == $_POST['device_id']) { echo("selected"); }
  echo(">".$data['hostname']."</option>");
}
?>
      </select>
    </div>
    <div class="form-group">
      <select name="searchby" id="searchby" class="form-control input-sm">
        <option value="mac" <?php if ($_POST['searchby'] != "ip") { echo("selected"); } ?> >MAC Address</option>
        <option value="ip" <?php if ($_POST['searchby'] == "ip") { echo("selected"); } ?> >IP Address</option>
      </select>
    </div>
    <div class="form-group">
      <input type="text" name="address" id="address" size=40 value="<?php echo($_POST['address']); ?>" class="form-control input-sm" placeholder="Address" />
    </div>
      <button type="submit" class="btn btn-default input-sm">Search</button>
  </form>

<?php

if(isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $results = $_POST['results'];
} else {
    $results = 50;
}

print_optionbar_end();

              echo('<form method="post" action="search/search=arp/" id="result_form">
              <table class="table table-hover table-condensed table-striped">
                <tr>
                  <td colspan="5"><strong>ARP Entries</strong></td>
                  <td><select name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">');
                  $result_options = array('10','50','100','250','500','1000','5000');
                  foreach($result_options as $option) {
                      echo "<option value='$option'";
                      if($results == $option) {
                          echo " selected";
                      }
                      echo ">$option</option>";
                  }
                  echo('</select></td>
                </tr>');

$count_query = "SELECT COUNT(M.port_id)";
$full_query = "SELECT *";
$query = " FROM `ipv4_mac` AS M, `ports` AS P, `devices` AS D WHERE M.port_id = P.port_id AND P.device_id = D.device_id ";
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
$count_query = $count_query . $query;
$count = dbFetchCell($count_query,$param);
if(!isset($_POST['page_number']) && $_POST['page_number'] < 1) {
    $page_number = 1;
} else {
    $page_number = $_POST['page_number'];
}
$start = ($page_number - 1) * $results;
$full_query = $full_query . $query . " LIMIT $start,$results";
echo('<tr><th>MAC Address</th><th>IP Address</th><th>Device</th><th>Interface</th><th>Remote device</th><th>Remote interface</th></tr>');
foreach (dbFetchRows($full_query, $param) as $entry)
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

    $arp_host = dbFetchRow("SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id", array($entry['ipv4_address']));
    if ($arp_host) { $arp_name = generate_device_link($arp_host); } else { unset($arp_name); }
    if ($arp_host) { $arp_if = generate_port_link($arp_host); } else { unset($arp_if); }
    if ($arp_host['device_id'] == $entry['device_id']) { $arp_name = "Localhost"; }
    if ($arp_host['port_id'] == $entry['port_id']) { $arp_if = "Local port"; }

    echo('<tr>
        <td>' . formatMac($entry['mac_address']) . '</td>
        <td>' . $entry['ipv4_address'] . '</td>
            <td>' . generate_device_link($entry) . '</td>
        <td>' . generate_port_link($entry, makeshortif(fixifname($entry['ifDescr']))) . ' ' . $error_img . '</td>
            <td>'.$arp_name.'</td>
        <td>'.$arp_if.'</td>
            </tr>');
  }

  unset($ignore);
}
if($count % $results > 0) {
    echo('    <tr>
         <td colspan="6" align="center">'. generate_pagination($count,$results,$page_number) .'</td>
     </tr>');
}
echo('</table>
<input type="hidden" name="page_number" id="page_number" value="'.$page_number.'">
<input type="hidden" name="results_amount" id="results_amount" value="'.$results.'">
</form>');
?>

<script type="text/javascript">
    function updateResults(results) {
       $('#results_amount').val(results.value);
       $('#page_number').val(1);
       $('#result_form').submit();
    }

    function changePage(page,e) {
        e.preventDefault();
        $('#page_number').val(page);
        $('#result_form').submit();
    }
</script>
