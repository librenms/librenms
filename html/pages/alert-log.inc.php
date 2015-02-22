<?php
$param = array();
if (isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $numresults = $_POST['results_amount'];
} else {
    $numresults = 250;
}
if (isset($_POST['page_number']) && $_POST['page_number'] > 0) {
    $page_number = $_POST['page_number'];
} else {
    $page_number = 1;
}
$start = ($page_number - 1) * $numresults;
$pagetitle[] = "Alert Log";
print_optionbar_start();
$where = "1";
if (is_numeric($_POST['device']))
{
  $where .= ' AND E.device_id = ?';
  $param[] = $_POST['device'];
}
if ($_POST['string'])
{
  $where .= " AND R.rule LIKE ?";
  $param[] = "%".$_POST['string']."%";
}
?>

<form method="post" action="" class="form-inline" role="form" id="result_form">
    <div class="form-group">
      <input type="text" name="string" id="string" value="<?php echo($_POST['string']); ?>" placeholder="Search" class="form-control input-sm" />
    </div>
    <div class="form-group">
      <label>
        <strong>Device</strong>
      </label>
      <select name="device" id="device" class="form-control input-sm">
        <option value="">All Devices</option>
        <?php
          foreach (get_all_devices() as $hostname)
          {
            echo("<option value='".getidbyname($hostname)."'");
            if (getidbyname($hostname) == $_POST['device']) { echo("selected"); }
            echo(">".$hostname."</option>");
          }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-default input-sm">Search</button>

<?php
print_optionbar_end();
if ($_SESSION['userlevel'] >= '5')
{
  $query = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id WHERE $where ORDER BY `time_logged` DESC";
} else {
  $query = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id RIGHT JOIN devices_perms AS P ON E.device_id = P.device_id WHERE $where AND P.user_id = ? ORDER BY `time_logged` DESC";

  $param[] = $_SESSION['user_id'];
}
$count_query = "SELECT COUNT(time_logged) $query";
$count = dbFetchCell($count_query,$param);
$full_query = "SELECT D.device_id,name,time_logged,DATE_FORMAT(time_logged, '%D %b %Y %T') as humandate $query LIMIT $start,$numresults";
            echo('<div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>Alert Log entries</strong>
                        </div>
                        <div class="col-md-8" align="center">'. generate_pagination($count,$numresults,$page_number) .'</div>
                        <div class="col-md-2">
                            <select name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">');
                            $result_options = array('10','50','100','250','500','1000','5000');
                            foreach($result_options as $option) {
                                echo "<option value='$option'";
                                if ($numresults == $option) {
                                    echo " selected";
                                }
                                echo ">$option</option>";
                            }
                        echo('
                            </select>
                        </div>
                    </div>
                </div>
            </div>
              <table class="table table-hover table-condensed table-striped">');
foreach (dbFetchRows($full_query, $param) as $alert_entry)
{
  include("includes/print-alerts.inc.php");
}
if ($count % $numresults > 0) {
    echo('    <tr>
         <td colspan="6" align="center">'. generate_pagination($count,$numresults,$page_number) .'</td>
     </tr>');
}
echo('</table>
<input type="hidden" name="page_number" id="page_number" value="'.$page_number.'">
<input type="hidden" name="results_amount" id="results_amount" value="'.$numresults.'">
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