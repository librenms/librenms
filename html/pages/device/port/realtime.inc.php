<?php

### FIXME - do this in a function and/or do it in graph-realtime.php

if($device['os'] == "linux") { 
  $interval = "15"; 
} else {
  $interval = "2";
}

?>

<div align="center">
<object data="graph-realtime.php?type=bits&id=<?php echo($interface['interface_id'] . "&interval=".$interval); ?>" type="image/svg+xml" width="800" height="375">
<param name="src" value="graph.php?type=bits&id=<?php echo($interface['interface_id'] . "&interval=".$interval); ?>" />
Your browser does not support the type SVG! You need to either use Firefox or download the Adobe SVG plugin.
</object>
</div>
