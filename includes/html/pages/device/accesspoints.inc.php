<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2024 LibreNMS
 * @author     LibreNMS Contributors
*/

echo '<form method="post" action="" id="result_form">';
echo csrf_field();
if (isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $results = $_POST['results'];
} else {
    $results = 50;
}

echo '<div class="panel panel-default">';
echo '<div class="pull-right" style="padding-top: 5px;padding-right: 5px;">';
echo '<select data-toggle="popover" data-placement="left" data-content="results per page" name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">';

$result_options = [
    '10',
    '50',
    '100',
    '250',
    '500',
    '1000',
    '5000',

];
foreach ($result_options as $option) {
    echo "<option value='$option'";
    if ($results == $option) {
        echo ' selected';
    }
    echo ">$option</option>";
}
echo '</select>';
echo '</div>';
echo '<div class="panel-heading">';
echo '<span class="tw-font-bold">Access Points</span>';
echo '</div>';
echo '</div>';
echo '<br>';
echo "<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";

$i = '1';

if ($vars['ap'] > 0) { //We have a selected AP
    $aps = dbFetchRows("SELECT * FROM `access_points` WHERE `device_id` = ? AND `accesspoint_id` = ? AND `deleted` = '0' ORDER BY `name`,`radio_number` ASC", [$device['device_id'], $vars['ap']]);
} else {
    $aps = dbFetchRows("SELECT * FROM `access_points` WHERE `device_id` = ? AND `deleted` = '0' ORDER BY `name`,`radio_number` ASC", [$device['device_id']]);
}

$count = count($aps);

if (isset($_POST['page_number']) && $_POST['page_number'] > 0 && $_POST['page_number'] <= $count) {
    $page_number = $_POST['page_number'];
} else {
    $page_number = 1;
}

$start = (($page_number - 1) * $results);


echo "<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
$index = 0;
foreach ($aps as $ap) {
    $index++;

    if ($index < $start) {
        continue;
    }
    if ($index > $start + $results) {
        break;
    }

    include 'includes/html/print-accesspoint.inc.php';
    $i++;
}

echo '</table></div>';
echo '</div>';

if ($count > $results) {
    echo '<div class="table-responsive">';
    echo '<div class="col pull-left">';
    echo generate_pagination($count, $results, $page_number);
    echo '</div>';
    echo '<div class="col pull-right">';
    $showing_start = ($page_number * $results) - $results + 1;
    $showing_end = $page_number * $results;
    if ($showing_end > $count) {
        $showing_end = $count;
    }
    echo "<p class=\"pagination\">Showing $showing_start to $showing_end of $count access pints</p>";
    echo '</div>';
    echo '</div>';
}

echo '<input type="hidden" name="page_number" id="page_number" value="' . htmlspecialchars($page_number) . '">
    <input type="hidden" name="results_amount" id="results_amount" value="' . htmlspecialchars($results) . '">
    </form>';
?>
<script>
function updateResults(results) {
    $('#results_amount').val(results.value);
    $('#page_number').val(1);
    $('#result_form').trigger( "submit" );
}

function changePage(page,e) {
    e.preventDefault();
    $('#page_number').val(page);
    $('#result_form').trigger( "submit" );
}
</script>
