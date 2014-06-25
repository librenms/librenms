<?php
/*
 * This file is part of LibreNMS
 *
 * Copyright (c) 2014 Bohdan Sanders <http://bohdans.com/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
?>

<script class="code" type="text/javascript">
$(document).ready(function() {
  $("#ToggleLogon").click( function()
  {
    document.getElementById('public-logon').style.display="block";
    document.getElementById('public-status').style.display="none";
  });
  $("#ToggleStatus").click( function()
  {
    document.getElementById('public-logon').style.display="none";
    document.getElementById('public-status').style.display="block";
  });
});
</script>

<?php

// Set Defaults here

$sql_param = array();
$pagetitle[] = "Public Devices";

$query = "SELECT * FROM `devices` WHERE 1 AND disabled='0' AND `ignore`='0' ORDER BY hostname";

?>
<div class="well"><h3>System Status<button class="btn btn-default" type="submit" style="float:right;" id="ToggleLogon">Logon</button></h3></div>
  <div class="panel panel-default panel-condensed">
    <div class="table-responsive">
      <table class="table table-condensed">
        <tr>
          <th></th>
          <th></th>
          <th>Device</th>
          <th></th>
          <th>Platform</th>
          <th>Uptime/Location</th>
        </tr>
<?php
foreach (dbFetchRows($query, $sql_param) as $device)
{
  include("includes/hostbox-public.inc.php");
}
?>
      </table>
    </div>
  </div>
