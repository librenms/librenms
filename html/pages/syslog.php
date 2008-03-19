<meta http-equiv="refresh" content="60">
<?

$sql = "SELECT *, DATE_FORMAT(datetime, '%D %b %T') AS date from syslog ORDER BY datetime DESC LIMIT 1000";

$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=2 width=100%>");

while($entry = mysql_fetch_array($query)) 
{
  include("includes/print-syslog.inc");
}

echo("</table>");

?>
</table>
