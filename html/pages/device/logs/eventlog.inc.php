<hr />
<form method="post" action="">
  <div class="row">
    <div class="col-md-4">
      <input type="text" name="string" placeholder="Search" class="form-control" id="string" value="<?php echo $_POST['string']; ?>" required/>
    </div>
    <div class="col-md-4">
        <select name="type" class="form-control" id="type">
          <option value="">All Types</option>
          <option value="system">System</option>
            <?php
            foreach (dbFetchRows('SELECT `type` FROM `eventlog` WHERE device_id = ? GROUP BY `type` ORDER BY `type`', array($device['device_id'])) as $data) {
                echo "<option value='".$data['type']."'";
                if ($data['type'] == $_POST['type']) {
                    echo 'selected';
                }

                echo '>'.$data['type'].'</option>';
            }
            ?>
        </select>
    </div>
    <div class="col-md-4"><input class="btn btn-default" type="submit" value="Search"></div>
  </div>
</form>

<?php
print_optionbar_end();

$sql = '';
if (!empty($_POST['string'])) {
    $sql .= " AND message LIKE '%".mres($_POST['string'])."%'";
}

$entries = dbFetchRows("SELECT *,DATE_FORMAT(datetime, '".$config['dateformat']['mysql']['compact']."') as humandate  FROM `eventlog` WHERE `host` = ? $sql ORDER BY `datetime` DESC LIMIT 0,250", array($device['device_id']));

echo '      <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Eventlog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">';

foreach ($entries as $entry) {
    include 'includes/print-event.inc.php';
}

echo '        </table>
            </div>';

$pagetitle[] = 'Events';
