<?php

print_optionbar_start('25');

?>

  <form method="post" action="">
  <label><strong>Search</strong>
    <input type="text" name="string" id="string" value="<?php  echo($_POST['string']); ?>" />
  </label>
  <label>
    <strong>Program</strong>
    <select name="program" id="program">
      <option value="">All Programs</option>
      <?php
        $query = mysql_query("SELECT `program` FROM `syslog` WHERE device_id = '" . $_GET['id'] . "' GROUP BY `program` ORDER BY `program`");
        while ($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['program']."'");
          if ($data['program'] == $_POST['program']) { echo("selected"); }
          echo(">".$data['program']."</option>");
        }
      ?>
    </select>
  </label>
  <input class=submit type=submit value=Search>
</form>

<?php

print_optionbar_end();

if ($_POST['string'])
{
  $where = " AND msg LIKE '%".$_POST['string']."%'";
}

if ($_POST['program'])
{
  $where .= " AND program = '".$_POST['program']."'";
}

$sql =  "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog WHERE device_id = '" . $_GET['id'] . "' $where";
$sql .= " ORDER BY timestamp DESC LIMIT 1000";
$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while ($entry = mysql_fetch_array($query)) { include("includes/print-syslog.inc.php"); }
echo("</table>");

?>