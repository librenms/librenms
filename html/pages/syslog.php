<meta http-equiv="refresh" content="60">
<div style="background-color: #eeeeee; padding: 10px;">
<form id="form1" name="form1" method="post" action="">
  <label><strong>Search</strong>
    <input type="text" name="search" id="search" />
  </label>
  <label>
    <strong>Program</strong>
    <select name="program" id="program">
      <option value="">All Programs</option>
      <?php
        $query = mysql_query("SELECT `program` FROM `syslog` GROUP BY `program` ORDER BY `program`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['program']."'>".$data['program']."</option>");
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
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['device_id']."'");
            
          echo(">".$data['hostname']."</option>");
        }
      ?>
    </select>
  </label>

  <input type=submit value=Search>

</form>
</div>

<?

$sql = "SELECT *, DATE_FORMAT(datetime, '%D %b %T') AS date from syslog ORDER BY datetime DESC LIMIT 1000";
$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while($entry = mysql_fetch_array($query)) { include("includes/print-syslog.inc"); }
echo("</table>");

?>
</table>
