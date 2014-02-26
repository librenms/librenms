<?php

if ($services['total'])
{
echo('<div class="container-fluid">');
  echo('<div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">');
  echo("<img src='images/16/cog.png'><strong> Services</strong>");
  echo('      </div>
              <table class="table table-hover table-condensed table-striped">');

  echo("
<tr>
<td><img src='images/16/cog.png'> $services[total]</td>
<td><img src='images/16/cog_go.png'> $services[up]</td>
<td><img src='images/16/cog_error.png'> $services[down]</td>
<td><img src='images/16/cog_disable.png'> $services[disabled]</td>
</tr>
<tr>
<td colspan='4'>");

  foreach (dbFetchRows("SELECT * FROM services WHERE device_id = ? ORDER BY service_type", array($device['device_id'])) as $data)
  {
    if ($data['service_status'] == "0" && $data['service_ignore'] == "1") { $status = "grey"; }
    if ($data['service_status'] == "1" && $data['service_ignore'] == "1") { $status = "green"; }
    if ($data['service_status'] == "0" && $data['service_ignore'] == "0") { $status = "red"; }
    if ($data['service_status'] == "1" && $data['service_ignore'] == "0") { $status = "blue"; }
    echo("$break<a class=$status>" . strtolower($data['service_type']) . "</a>");
    $break = ", ";
  }
  echo('</td></tr></table>');
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
}

?>
