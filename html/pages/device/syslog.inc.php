<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 5px;'>
  <b class='rounded'>
  <b class='rounded1'></b>
  <b class='rounded2'></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 5px 5px; padding-left: 11px; clear: both; display:block;'>
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
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['program']."'");
          if($data['program'] == $_POST['program']) { echo("selected"); }
          echo(">".$data['program']."</option>");
        }
      ?>
    </select>
  </label>

  <input type=submit value=Search>

</form>
</div>
</div>
  <b class='rounded'>
  <b class='rounded5'></b>
  <b class='rounded4'></b>
  <b class='rounded3'></b>
  <b class='rounded2'></b>
  <b class='rounded1'></b></b>
</div>


<?php

if($_POST['string']) {
  $where = " AND msg LIKE '%".$_POST['string']."%'";
}

if($_POST['program']) {
  $where .= " AND program = '".$_POST['program']."'";
}

$sql =  "SELECT *, DATE_FORMAT(datetime, '%D %b %T') AS date from syslog WHERE device_id = '" . $_GET['id'] . "' $where";
$sql .= " ORDER BY datetime DESC LIMIT 1000";
$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while($entry = mysql_fetch_array($query)) { include("includes/print-syslog.inc"); }
echo("</table>");


?>
