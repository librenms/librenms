<?

$sql = "SELECT * from entPhysical AS E, devices AS D WHERE E.device_id = D.device_id";

if($_POST['search']) { $sstring = $_POST['search']; $sql = "SELECT * from entPhysical AS E, devices AS D WHERE E.entPhysicalModelName LIKE '$sstring' AND E.device_id = D.device_id"; }

echo("<div style='float: right;'><form method=post><b>Search</b> 
                                   <input name=search>$sstring</input>
                                   <input type=submit name=submit value=Search></form></div>");


$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");

echo("<tr><th>Hostname</th><th>Description</th><th>Name</th><th>Part No</th><th>Serial No</th></tr>");

while($entry = mysql_fetch_array($query)) { 
if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }
echo("<tr style=\"background-color: $bg\"><td>" . generatedevicelink($entry, shortHost($entry['hostname'])) . "</td><td>" . $entry['entPhysicalDescr']  . "</td><td>" . $entry['entPhysicalName']  . "</td><td>" . $entry['entPhysicalModelName']  . "</td><td>" . $entry['entPhysicalSerialNum'] . "</td></tr>");

}
echo("</table>");

?>
</table>

