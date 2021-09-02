<?php

require_once 'includes/html/modal/new_customoid.inc.php';
require_once 'includes/html/modal/delete_customoid.inc.php';

$no_refresh = true;

?>

<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<form method="post" action="" id="oid_form">

<?php

echo csrf_field();
if (isset($_POST['num_of_rows']) && $_POST['num_of_rows'] > 0) {
    $rows = $_POST['rows'];
} else {
    $rows = 10;
}
?>

<div class="table-responsive">
  <table class="table table-hover table-condensed" width="100%">
    <tr>
      <th>Name</th>
      <th>OID</th>
      <th>Value</th>
      <th>Unit</th>
      <th colspan="2">Alert Threshold</th>
      <th colspan="2">Warning Threshold</th>
      <th>Alerts</th>
      <th>Passed</th>
      <th style="width:86px;">Action</th>
    </tr>

<?php
echo '<tr>
<td colspan="4">
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-oid-form" data-device_id="' . $device['device_id'] . '"' . (Auth::user()->hasGlobalAdmin() ? '' : ' disabled') . '><i class="fa fa-plus"></i> Add New OID</button>
</td>
<th><small>High</small></th>
<th><small>Low</small></th>
<th><small>High</small></th>
<th><small>Low</small></th>
<td></td>
<td></td>
<td><select name="rows" id="rows" class="form-control input-sm" onChange="updateResults(this);">';

$num_of_rows_options = [
    '10',
    '25',
    '50',
    '100',
];
foreach ($num_of_rows_options as $option) {
    echo "<option value='" . $option . "'" . ($rows == $option ? ' selected' : '') . '>' . $option . '</option>';
}

echo '</select></td>
</tr>';

$query = 'FROM customoids';
$where = '';
$param = [];
if (isset($device['device_id']) && $device['device_id'] > 0) {
    $where = 'WHERE (device_id=?)';
    $param[] = $device['device_id'];
}

$count = dbFetchCell("SELECT COUNT(*) $query $where", $param);
if (isset($_POST['page_num']) && $_POST['page_num'] > 0 && $_POST['page_num'] <= $count) {
    $page_num = $_POST['page_num'];
} else {
    $page_num = 1;
}

$start = (($page_num - 1) * $rows);
$full_query = "SELECT * $query $where ORDER BY customoid_descr ASC LIMIT $start,$rows";

foreach (dbFetchRows($full_query, $param) as $oid) {
    echo "<tr class='" . $oid['customoid_id'] . "' id='row_" . $oid['customoid_id'] . "'>";
    echo '<td>' . $oid['customoid_descr'] . '</td>';
    echo '<td>' . $oid['customoid_oid'] . '</td>';
    echo '<td>' . $oid['customoid_current'] . '</td>';
    echo '<td>' . $oid['customoid_unit'] . '</td>';
    echo '<td>' . $oid['customoid_limit'] . '</td>';
    echo '<td>' . $oid['customoid_limit_low'] . '</td>';
    echo '<td>' . $oid['customoid_limit_warn'] . '</td>';
    echo '<td>' . $oid['customoid_limit_low_warn'] . '</td>';
    echo "<td><input id='" . $oid['customoid_id'] . "' type='checkbox' name='alert'" . ($oid['customoid_alert'] ? ' checked' : '') . ' disabled></td>';
    echo "<td><input id='" . $oid['customoid_id'] . "' type='checkbox' name='passed'" . ($oid['customoid_passed'] ? ' checked' : '') . ' disabled></td>';
    echo '<td>';
    echo "<div class='btn-group btn-group-sm' role='group'>";
    echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#create-oid-form' data-customoid_id='" . $oid['customoid_id'] . "' name='edit-oid' data-content='' data-container='body'" . (Auth::user()->hasGlobalAdmin() ? '' : ' disabled') . "><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button>";
    echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-toggle='modal' data-target='#delete-oid-form' data-customoid_id='" . $oid['customoid_id'] . "' name='delete-oid' data-content='' data-container='body'><i class='fa fa-lg fa-trash' aria-hidden='true'" . (Auth::user()->hasGlobalAdmin() ? '' : ' disabled') . '></i></button>';
    echo '</div>';
    echo '</td>';
    echo "</tr>\r\n";
}//end foreach

if (($count % $rows) > 0) {
    echo '<tr>
        <td colspan="11" align="center">' . generate_pagination($count, $rows, $page_num) . '</td>
        </tr>';
}

echo '</table>
</div>
<input type="hidden" name="page_num" id="page_num" value="' . $page_num . '">
<input type="hidden" name="num_of_rows" id="num_of_rows" value="' . $rows . '">
</form>';

?>

<script>

$("[data-toggle='modal'], [data-toggle='popover']").popover({
    trigger: 'hover',
        'placement': 'top'
});

function updateResults(rows) {
    $('#num_of_rows').val(rows.value);
    $('#page_num').val(1);
    $('#oid_form').trigger( "submit" );
}

function changePage(page,e) {
    e.preventDefault();
    $('#page_num').val(page);
    $('#oid_form').trigger( "submit" );
}

</script>
