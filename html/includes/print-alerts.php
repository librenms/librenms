<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php
require_once('includes/modal/new_alert_rule.inc.php');
?>
<form method="post" action="" id="result_form">
<?php

if(isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $results = $_POST['results'];
} else {
    $results = 50;
}

echo '<div class="table-responsive">
<table class="table table-hover table-condensed" width="100%">
  <tr>
    <th>#</th>
    <th>Rule</th>
    <th>Hostname</th>
    <th>Timestamp</th>
    <th>Severity</th>
    <th>Status</th>
    <th>Acknowledge</th>
  </tr>';

echo ('<td colspan="6">');
if ($_SESSION['userlevel'] == '10') {
    echo('<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-alert" data-device_id="'. $device['device_id'] .'">Create new alert rule</button>');
}
echo ('</td>
<td><select name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">');
$result_options = array('10','50','100','250','500','1000','5000');
foreach($result_options as $option) {
    echo "<option value='$option'";
    if($results == $option) {
        echo " selected";
    }
    echo ">$option</option>";
}
echo('</select></td>');

$rulei=1;
$count_query = "SELECT COUNT(alerts.id)";
$full_query = "SELECT alerts.*, devices.hostname";
$sql = '';
$param = array();
if(isset($device['device_id']) && $device['device_id'] > 0) {
    $sql = 'AND `alerts`.`device_id`=?';
    $param = array($device['device_id']);
}
$query = " FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id` RIGHT JOIN alert_rules ON alerts.rule_id=alert_rules.id WHERE `state`= 1 $sql ORDER BY `alerts`.`timestamp` DESC";
$count_query = $count_query . $query;
$count = dbFetchCell($count_query,$param);
if(!isset($_POST['page_number']) && $_POST['page_number'] < 1) {
    $page_number = 1;
} else {
    $page_number = $_POST['page_number'];
}
$start = ($page_number - 1) * $results;
$full_query = $full_query . $query . " LIMIT $start,$results";

foreach( dbFetchRows($full_query, $param) as $alert ) {
	$rule = dbFetchRow("SELECT * FROM alert_rules WHERE id = ? LIMIT 1", array($alert['rule_id']));
	$ico = "ok";
	$col = "green";
	$extra = "";
	if( (int) $alert['state'] === 0 ) {
		$ico = "ok";
		$col = "green";
	} elseif( (int) $alert['state'] === 1 ) {
		$ico = "remove";
		$col = "red";
		$extra = "danger";
	} elseif( (int) $alert['state'] === 2 ) {
		$ico = "time";
		$col = "#800080";
		$extra = "warning";
	}
        $alert_checked = '';
        $orig_ico = $ico;
        $orig_col = $col;
        $orig_class = $extra;
	echo "<tr class='".$extra."' id='row_".$alert['id']."'>";
	echo "<td><i>#".((int) $rulei++)."</i></td>";
	echo "<td><i>".htmlentities($rule['rule'])."</i></td>";
        echo "<td>".$alert['hostname']."</td>";
	echo "<td>".($alert['timestamp'] ? $alert['timestamp'] : "N/A")."</td>";
	echo "<td>".$rule['severity']."</td>";
	echo "<td><i id='alert-rule-".$rule['id']."' class='glyphicon glyphicon-".$ico."' style='color:".$col."; font-size: 24px;' >&nbsp;</i></td>";
        echo "<td>";
        if ($_SESSION['userlevel'] == '10') {
            echo "<button type='button' class='btn btn-warning btn-sm'  data-target='#ack-alert' data-alert_id='".$alert['id']."' name='ack-alert' id='ack-alert'><span class='glyphicon glyphicon-volume-off' aria-hidden='true'></span></button>";
        }
        echo "</td>";
	echo "</tr>\r\n";
}
if($count % $results > 0) {
    echo('    <tr>
         <td colspan="7" align="center">'. generate_pagination($count,$results,$page_number) .'</td>
     </tr>');
}
echo '</table>
<input type="hidden" name="page_number" id="page_number" value="'.$page_number.'">
<input type="hidden" name="results_amount" id="results_amount" value="'.$results.'">
</form>
</div>';
?>

<script>
$('#ack-alert').click('', function(e) {
    event.preventDefault();
    var alert_id = $(this).data("alert_id");
    $.ajax({
        type: "POST",
        url: "/ajax_form.php",
        data: { type: "ack-alert", alert_id: alert_id },
        success: function(msg){
            $("#message").html('<div class="alert alert-info">'+msg+'</div>');
            if(msg.indexOf("ERROR:") <= -1) {
                location.reload();
            }
        },
        error: function(){
             $("#message").html('<div class="alert alert-info">An error occurred acking this alert.</div>');
        }
    });
});

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
