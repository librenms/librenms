<?

$query = "SELECT * FROM `syslog` WHERE `host` = '$_GET[id]' ORDER BY `datetime` DESC LIMIT 0,250";
$data = mysql_query($query);

echo("<table cellspacing=0 cellpadding=1 width=100%>");

while($entry = mysql_fetch_array($data)) {

include("includes/print-syslog.inc");

}
echo("</table>");

 ?>
