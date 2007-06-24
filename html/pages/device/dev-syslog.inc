<?

$query = "SELECT *, DATE(datetime) AS date, TIME(datetime) AS time ";
$query .= "FROM `syslog` WHERE `host` = '" . $_GET['id'] . "'";
if($search) { $query .= " AND `msg` LIKE '%" . $_POST['search'] . "%'"; }
$query .= " ORDER BY `datetime` desc LIMIT 0,500";

$data = mysql_query($query);


echo("<div align=right><form id='form1' name='form1' method='post' action='" . $_SERVER['REQUEST_URI'] . "'>
  <label>
  <input type='text' name='search' id='search' />
  <input type='submit' name='button' id='button' value='Search' />
  </label>
</form></div>");

echo("<table cellspacing=0 cellpadding=2 width=100%>");

while($entry = mysql_fetch_array($data)) {
  include("includes/print-syslog.inc");
}

echo("</table>");

?>
