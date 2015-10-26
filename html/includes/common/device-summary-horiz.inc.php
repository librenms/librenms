<?php
require_once 'includes/object-cache.inc.php';

$temp_output = '
<div class="panel panel-default panel-condensed table-responsive">
<table class="table table-hover table-condensed table-striped">
  <thead>
    <tr class="info">
      <th>&nbsp;</th>
      <th><span class="grey">Total</span></th>
      <th><span class="green">Up</span></th>
      <th><span class="red">Down</span></th>
      <th><span class="grey">Ignored</span></th>
      <th><span class="black">Disabled</span></th>
      '.($config['summary_errors'] ? '<th>Errored</th>' : '').'
    </tr>
  </thead>
  <tbody>
    <tr class="active">
      <td><a href="devices/">Devices</a></td>
      <td><a href="devices/"><span>'.$devices['count'].'</span></a></td>
      <td><a href="devices/state=up/format=list_detail/"><span class="green"> '.$devices['up'].'</span></a></td>
      <td><a href="devices/state=down/format=list_detail/"><span class="red"> '.$devices['down'].'</span></a></td>
      <td><a href="devices/ignore=1/format=list_detail/"><span class="grey"> '.$devices['ignored'].'</span></a></td>
      <td><a href="devices/disabled=1/format=list_detail/"><span class="black"> '.$devices['disabled'].'</span></a></td>
      '.($config['summary_errors'] ? '<td>-</td>' : '').'
    </tr>
    <tr class="active">
      <td><a href="ports/">Ports</a></td>
      <td><a href="ports/"><span>'.$ports['count'].'</span></a></td>
      <td><a href="ports/format=list_detail/state=up/"><span class="green"> '.$ports['up'].'</span></a></td>
      <td><a href="ports/format=list_detail/state=down/"><span class="red"> '.$ports['down'].'</span></a></td>
      <td><a href="ports/format=list_detail/ignore=1/"><span class="grey"> '.$ports['ignored'].'</span></a></td>
      <td><a href="ports/format=list_detail/state=admindown/"><span class="black"> '.$ports['shutdown'].'</span></a></td>
      '.($config['summary_errors'] ? '<td><a href="ports/format=list_detail/errors=1/"><span class="black"> '.$ports['errored'].'</span></a></td>' : '').'
    </tr>';
if ($config['show_services']) {

$temp_output .= '
    <tr class="active">
      <td><a href="services/">Services</a></td>
      <td><a href="services/"><span>'.$services['count'].'</span></a></td>
      <td><a href="services/state=up/view=details/"><span class="green">'.$services['up'].'</span></a></td>
      <td><a href="services/state=down/view=details/"><span class="red"> '.$services['down'].'</span></a></td>
      <td><a href="services/ignore=1/view=details/"><span class="grey"> '.$services['ignored'].'</span></a></td>
      <td><a href="services/disabled=1/view=details/"><span class="black"> '.$services['disabled'].'</span></a></td>
      '.($config['summary_errors'] ? '<td>-</td>' : '').'
    </tr>';
}
$temp_output .= '
  </tbody>
</table>
</div>
';

unset($common_output);
$common_output[] = $temp_output;
