<meta http-equiv="refresh" content="60">

<?php if ($_GET['opta'] == "expunge" && $_SESSION['userlevel'] >= '10') { dbFetchCell("TRUNCATE TABLE `syslog`"); } ?>

<?php print_optionbar_start('25'); ?>

<form method="post" action="">
  <span style="font-weight: bold;">Syslog</span> &#187;
  <label><strong>Search</strong>
    <input type="text" name="string" id="string" value="<?php echo($_POST['string']); ?>" />
  </label>
  <label>
    <strong>Program</strong>
    <select name="program" id="program">
      <option value="">All Programs</option>
      <?php
        foreach (dbFetchRows("SELECT `program` FROM `syslog` GROUP BY `program` ORDER BY `program`") as $data)
        {
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
        foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $data)
        {
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
  $where = " AND S.msg LIKE ?";
  $array[] = "%".$_POST['string']."%";
}

if ($_POST['program'])
{
  $where .= " AND S.program = ?";
  $array[] = $_POST['program'];
}

if (is_numeric($_POST['device']))
{
  $where .= " AND S.device_id = ?";
  $array[] = $_POST['device'];
}

if ($_SESSION['userlevel'] >= '5')
{
  $sql = "SELECT *, DATE_FORMAT(timestamp, '%Y-%m-%d %T') AS date from syslog AS S";
  if (count($array))
  {
    $sql .= " WHERE 1 ".$where;
  }
  $sql .= " ORDER BY timestamp DESC LIMIT 1000";
} else {
  $sql  = "SELECT *, DATE_FORMAT(timestamp, '%Y-%m-%d %T') AS date from syslog AS S, devices_perms AS P";
  $sql .= "WHERE S.device_id = P.device_id AND P.user_id = ?";
  if (count($array))
  {
    $sql .= " WHERE 1 ".$where;
  }
  $sql .= " ORDER BY timestamp DESC LIMIT 1000";

  $array = array_merge(array($_SESSION['user_id']), $array);
}

echo("<table cellspacing=0 cellpadding=2 width=100%>");
foreach (dbFetchRows($sql, $array) as $entry)
{
  $entry = array_merge($entry, device_by_id_cache($entry['device_id']));
  include("includes/print-syslog.inc.php");
}
echo("</table>");

?>
