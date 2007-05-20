<?

if($search) {
  $query = "SELECT * FROM `syslog` WHERE `host` = '" . $_GET['id'] . "' AND `msg` LIKE '%" . $_POST['search'] . "%' ORDER BY `seq` desc";
} else {
  $query = "SELECT * FROM `syslog` WHERE `host` = '" . $_GET['id'] . "' ORDER BY `seq` desc LIMIT 0,500";
}

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
