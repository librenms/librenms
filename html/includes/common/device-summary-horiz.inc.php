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
      '.($config['summary_errors'] ? '<th>Errored</th>' : '').'
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><a href="devices/">Devices</a></td>
      <td><a href="devices/"><center><span class="label label-primary">'.$devices['count'].'</span></a></td>
      <td><a href="devices/state=up/format=list_detail/"><center><span class="label label-success"> '.$devices['up'].'</span></a></center></td>
      <td><a href="devices/state=down/format=list_detail/"><center><span class="label label-danger"> '.$devices['down'].'</span></a></center></td>
      <td><a href="devices/ignore=1/format=list_detail/"><center><span class="label label-info"> '.$devices['ignored'].'</span></a></center></td>
      <td><a href="devices/disabled=1/format=list_detail/"><center><span class="label label-default"> '.$devices['disabled'].'</span></a></center></td>
      '.($config['summary_errors'] ? '<td>-</td>' : '').'
    </tr>
    <tr>
      <td><a href="ports/">Ports</a></td>
      <td><a href="ports/"><center><span class="label label-primary">'.$ports['count'].'</span></a></center></td>
      <td><a href="ports/format=list_detail/state=up/"><center><span class="label label-success"> '.$ports['up'].'</span></a></center></td>
      <td><a href="ports/format=list_detail/state=down/"><center><span class="label label-danger"> '.$ports['down'].'</span></a></center></td>
      <td><a href="ports/format=list_detail/ignore=1/"><center><span class="label label-info"> '.$ports['ignored'].'</span></a></center></td>
      <td><a href="ports/format=list_detail/state=admindown/"><center><span class="label label-default"> '.$ports['shutdown'].'</span></a></center></td>
      '.($config['summary_errors'] ? '<td><a href="ports/format=list_detail/errors=1/"><center><span class="black"> '.$ports['errored'].'</span></a></center></td>' : '').'
    </tr>';
if ($config['show_services']) {

$temp_output .= '
    <tr class="active">
      <td><a href="services/">Services</a></td>
      <td><a href="services/"><center><span class="label label-primary">'.$services['count'].'</span></a></center></td>
      <td><a href="services/state=up/view=details/"><center><span class="label label-success">'.$services['up'].'</span></a></td>
      <td><a href="services/state=down/view=details/"><center><span class="label label-danger"> '.$services['down'].'</span></a></center></td>
      <td><a href="services/ignore=1/view=details/"><center><span class="label label-info"> '.$services['ignored'].'</span></a></center></td>
      <td><a href="services/disabled=1/view=details/"><center><span class="label label-default"> '.$services['disabled'].'</span></a></center></td>
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
