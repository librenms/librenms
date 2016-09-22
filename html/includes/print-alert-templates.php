<?php

$no_refresh = true;

?>

<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php
require_once 'includes/modal/alert_template.inc.php';
require_once 'includes/modal/delete_alert_template.inc.php';
require_once 'includes/modal/attach_alert_template.inc.php';
?>

<form method="post" action="" id="result_form">
<?php
if (isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $results = $_POST['results'];
} else {
    $results = 50;
}

echo '<div class="table-responsive">
<table class="table table-hover table-condensed" width="100%">
  <tr>
    <th>Name</th>
    <th>Action</th>
  </tr>
  <tr>
    <td>';

if ($_SESSION['userlevel'] >= '10') {
    echo '<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#alert-template" data-template_id="">Create new alert template</button>';
}

echo '</td>
<td><select name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">';
$result_options = array(
                   '10',
                   '50',
                   '100',
                   '250',
                   '500',
                   '1000',
                   '5000',
                  );
foreach ($result_options as $option) {
    echo "<option value='$option'";
    if ($results == $option) {
        echo ' selected';
    }

    echo ">$option</option>";
}

echo '</select></td>';

$count_query = 'SELECT COUNT(id)';
$full_query  = 'SELECT *';

$query = ' FROM `alert_templates`';

$count_query = $count_query.$query;
$count       = dbFetchCell($count_query, $param);
if (!isset($_POST['page_number']) && $_POST['page_number'] < 1) {
    $page_number = 1;
} else {
    $page_number = $_POST['page_number'];
}

$start      = (($page_number - 1) * $results);
$full_query = $full_query.$query." LIMIT $start,$results";

foreach (dbFetchRows($full_query, $param) as $template) {
    echo '<tr id="row_'.$template['id'].'">
            <td>'.$template['name'].'</td>
            <td>';
    if ($_SESSION['userlevel'] >= '10') {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#alert-template' data-template_id='".$template['id']."' data-template_action='edit' name='edit-alert-template'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirm-delete-alert-template' data-template_id='".$template['id']."' name='delete-alert-template'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#attach-alert-template' data-template_id='".$template['id']."' name='attach-alert-template'><i class='fa fa-th-list' aria-hidden='true'></span></button>";
        echo "</div>";
    }

    echo '    </td>
          </tr>';
}

if (($count % $results) > 0) {
    echo '    <tr>
                  <td colspan="2" align="center">'.generate_pagination($count, $results, $page_number).'</td>
              </tr>';
}

echo '</table>
<input type="hidden" name="page_number" id="page_number" value="'.$page_number.'">
<input type="hidden" name="results_amount" id="results_amount" value="'.$results.'">
</form>
</div>';
