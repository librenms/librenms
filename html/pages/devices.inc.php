<?php

if ($_POST['hostname']) { $where = " AND hostname LIKE '%".mres($_POST['hostname'])."%'"; }
if ($_POST['os'])       { $where = " AND os = '".mres($_POST['os'])."'"; }
if ($_POST['version'])  { $where .= " AND version = '".mres($_POST['version'])."'"; }
if ($_POST['hardware']) { $where .= " AND hardware = '".mres($_POST['hardware'])."'"; }
if ($_POST['features']) { $where .= " AND features = '".mres($_POST['features'])."'"; }

# FIXME override
if (isset($_REQUEST['location']))
{
  if ($_GET['location'] == "Unset") { $location_filter = ''; }
  if ($_GET['location'] && !isset($_POST['location']))  { $location_filter = $_GET['location']; }
  if ($_POST['location']) { $location_filter = $_POST['location']; }
}

print_optionbar_start(62);
?>
<form method="post" action="">
  <table cellpadding="4" cellspacing="0" class="devicetable" width="100%">
    <tr>
      <td width="30" align="center" valign="middle"></td>
      <td width="300"><span style="font-weight: bold; font-size: 14px;"></span>
        <input type="text" name="hostname" id="hostname" size="40" value="<?php echo($_POST['hostname']); ?>" />
      </td>
      <td width="200">
        <select name='os' id='os'>
          <option value=''>All OSes</option>
          <?php
$query = mysql_query("SELECT `os` FROM `devices` AS D WHERE 1 GROUP BY `os` ORDER BY `os`");
while ($data = mysql_fetch_array($query))
{
  if ($data['os'])
  {
    echo("<option value='".$data['os']."'");
    if ($data['os'] == $_POST['os']) { echo(" selected"); }
    echo(">".$config['os'][$data['os']]['text']."</option>");
  }
}
          ?>
        </select>
        <br />
        <select name='version' id='version'>
          <option value=''>All Versions</option>
          <?php
$query = mysql_query("SELECT `version` FROM `devices` AS D WHERE 1 GROUP BY `version` ORDER BY `version`");
while ($data = mysql_fetch_array($query))
{
  if ($data['version'])
  {
    echo("<option value='".$data['version']."'");
    if ($data['version'] == $_POST['version']) { echo(" selected"); }
    echo(">".$data['version']."</option>");
  }
}
          ?>
        </select>
      </td>
      <td width="200">
        <select name="hardware" id="hardware">
          <option value="">All Platforms</option>
          <?php
$query = mysql_query("SELECT `hardware` FROM `devices` AS D WHERE 1 GROUP BY `hardware` ORDER BY `hardware`");
while ($data = mysql_fetch_array($query))
{
  if ($data['hardware'])
  {
    echo('<option value="'.$data['hardware'].'"');
    if ($data['hardware'] == $_POST['hardware']) { echo(" selected"); }
    echo(">".$data['hardware']."</option>");
  }
}
          ?>
        </select>
        <br />
        <select name="features" id="features">
          <option value="">All Featuresets</option>
          <?php
$query = mysql_query("SELECT `features` FROM `devices` AS D WHERE 1 GROUP BY `features` ORDER BY `features`");
while ($data = mysql_fetch_array($query))
{
  if ($data['features'])
  {
    echo('<option value="'.$data['features'].'"');
    if ($data['features'] == $_POST['features']) { echo(" selected"); }
    echo(">".$data['features']."</option>");
  }
}
          ?>
        </select>
      </td>
      <td>
        <select name="location" id="location">
          <option value="">All Locations</option>
          <?php
foreach (getlocations() as $location)
{
  if ($location)
  {
    echo('<option value="'.$location.'"');
    if ($location == $_POST['location']) { echo(" selected"); }
    echo(">".$location."</option>");
  }
}
          ?>
        </select>
        <input class="submit" type="submit" class="submit" value="Search">
      </td>
      <td width="10"></td>
    </tr>
  </table>
</form>

<?php
print_optionbar_end();

$sql = "SELECT * FROM devices WHERE 1 $where ORDER BY `ignore`, `status`, `hostname`";
if ($_GET['status'] == "alerted")
{
  $sql = "SELECT * FROM devices " . $device_alert_sql . " GROUP BY `device_id` ORDER BY `ignore`, `status`, `os`, `hostname`";
}

echo('<table cellpadding="7" cellspacing="0" class="devicetable sortable" width="100%">
<tr class="tablehead"><th></th><th>Device</th><th></th><th>Operating System</th><th>Platform</th><th>Uptime</th></tr>');

$device_query = mysql_query($sql);
while ($device = mysql_fetch_assoc($device_query))
{
  if (device_permitted($device['device_id']))
  {
    if (!$location_filter || ((get_dev_attrib($device,'override_sysLocation_bool') && get_dev_attrib($device,'override_sysLocation_string') == $location_filter)
      || $device['location'] == $location_filter))
    {
      include("includes/hostbox.inc.php");
    }
  }
}

echo("</table>");

?>
