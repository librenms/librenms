<?php
require_once 'includes/object-cache.inc.php';

$temp_output = '
<div class="panel panel-default panel-condensed table-responsive">
<table class="table table-hover table-condensed table-striped">
  <thead>
    <tr class="info">
      <th>&nbsp;</th>
      <th>Total</th>
      <th>Up</th>
      <th>Down</th>
      <th>Ignored</th>
      <th>Disabled</th>
    </tr>
  </thead>
  <tbody>
    <tr class="active">
      <td><a href="devices/">Devices</a></td>
      <td><a href="devices/"><span>'.$devices['count'].'</span></a></td>
      <td><a href="devices/state=up/format=list_detail/"><span class="green"> '.$devices['up'].' up</span></a></td>
      <td><a href="devices/state=down/format=list_detail/"><span class="red"> '.$devices['down'].' down</span></a></td>
      <td><a href="devices/ignore=1/format=list_detail/"><span class="grey"> '.$devices['ignored'].' ignored </span></a></td>
      <td><a href="devices/disabled=1/format=list_detail/"><span class="black"> '.$devices['disabled'].' disabled</span></a></td>
    </tr>
    <tr class="active">
      <td><a href="ports/">Ports</a></td>
      <td><a href="ports/"><span>'.$ports['count'].'</span></a></td>
      <td><a href="ports/format=list_detail/state=up/"><span class="green"> '.$ports['up'].' up </span></a></td>
      <td><a href="ports/format=list_detail/state=down/"><span class="red"> '.$ports['down'].' down </span></a></td>
      <td><a href="ports/format=list_detail/ignore=1/"><span class="grey"> '.$ports['ignored'].' ignored </span></a></td>
      <td><a href="ports/format=list_detail/state=admindown/"><span class="black"> '.$ports['shutdown'].' shutdown</span></a></td>
    </tr>';
if ($config['show_services']) {

$temp_output .= '
    <tr class="active">
      <td><a href="services/">Services</a></td>
      <td><a href="services/"><span>'.$services['count'].'</span></a></td>
      <td><a href="services/state=up/view=details/"><span class="green">'.$services['up'].' up</span></a></td>
      <td><a href="services/state=down/view=details/"><span class="red"> '.$services['down'].' down</span></a></td>
      <td><a href="services/ignore=1/view=details/"><span class="grey"> '.$services['ignored'].' ignored</span></a></td>
      <td><a href="services/disabled=1/view=details/"><span class="black"> '.$services['disabled'].' disabled</span></a></td>
    </tr>';
}
$temp_output .= '
  </tbody>
</table>
</div>
';

unset($common_output);
$common_output[] = $temp_output;
