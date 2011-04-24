<?

$device = device_by_id_cache($id);
$query = mysql_query("SELECT * FROM `processors` where `device_id` = '".$id."'");

if($config['os'][$device['os']]['processor_stacked'] == 1)
{
  include("includes/graphs/device/processor_stack.inc.php");
} else {
  include("includes/graphs/device/processor_separate.inc.php");
}

?>
