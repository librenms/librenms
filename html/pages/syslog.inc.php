<meta http-equiv="refresh" content="60">

<?php if ($_GET['opta'] == "expunge" && $_SESSION['userlevel'] >= '10') { mysql_query("TRUNCATE TABLE `syslog`"); } ?>

<?php print_optionbar_start('25'); ?>

<form method="post" action="">
  <label><strong>Search</strong>
    <input type="text" name="string" id="string" value="<?php  echo($_POST['string']); ?>" />
  </label>
  <label>
    <strong>Program</strong>
    <select name="program" id="program">
      <option value="">All Programs</option>
      <?php
        $query = mysql_query("SELECT `program` FROM `syslog` GROUP BY `program` ORDER BY `program`");
        while ($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['program']."'");
	  if ($data['program'] == $_POST['program']) { echo("selected"); }
          echo(">".$data['program']."</option>");
        }
      ?>
    </select>
  </label>
  <label>
    <strong>Device</strong>
    <select name="device" id="device">
      <option value="">All Devices</option>
      <?php
        $query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");
        while ($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['device_id']."'");

	  if ($data['device_id'] == $_POST['device']) { echo("selected"); }

          echo(">".$data['hostname']."</option>");
        }
      ?>
    </select>
  </label>

  <input type=submit class=submit value=Search>

</form>

<?php

print_optionbar_end();

if ($_POST['string'])
{
  $where = " AND S.msg LIKE '%".$_POST['string']."%'";
}

if ($_POST['program'])
{
  $where .= " AND S.program = '".$_POST['program']."'";
}

if ($_POST['device'])
{
  $where .= " AND D.device_id = '".$_POST['device']."'";
}

if ($_SESSION['userlevel'] >= '5')
{
  $sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog
          WHERE 1 $where ORDER BY timestamp DESC LIMIT 1000";
} else {
  $sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog AS S, devices_perms AS P
          WHERE S.device_id = P.device_id AND P.user_id = " . $_SESSION['user_id'] . " $where ORDER BY timestamp DESC LIMIT 1000";
}

$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while ($entry = mysql_fetch_array($query))
{
  $entry = array_merge($entry, device_by_id_cache($entry['device_id']));
  include("includes/print-syslog.inc.php");
}
echo("</table>");

?>
</table>