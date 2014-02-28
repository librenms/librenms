
  <hr />

  <form method="post" action="">
  <label><strong>Search</strong>
    <input type="text" name="string" id="string" value="<?php echo($_POST['string']); ?>" />
  </label>
  <label>
    <strong>Program</strong>
    <select name="program" id="program">
      <option value="">All Programs</option>
      <?php
        $datas = dbFetchRows("SELECT `program` FROM `syslog` WHERE device_id = ? GROUP BY `program` ORDER BY `program`", array($device['device_id']));
        foreach ($datas as $data) {
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

$param = array($device['device_id']);

if ($_POST['string'])
{
  $where   = " AND msg LIKE ?";
  $param[] = "%".$_POST['string']."%";
}

if ($_POST['program'])
{
  $where  .= " AND program = ?";
  $param[] = $_POST['program'];
}

$sql =  "SELECT *, DATE_FORMAT(timestamp, '%Y-%m-%d %T') AS date from syslog WHERE device_id = ? $where";
$sql .= " ORDER BY timestamp DESC LIMIT 1000";
echo('      <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Eventlog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');
foreach (dbFetchRows($sql, $param) as $entry) { include("includes/print-syslog.inc.php"); }
echo('        </table>
            </div>');
$pagetitle[] = "Syslog";

?>
