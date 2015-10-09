<?php
require_once 'includes/object-cache.inc.php';

$temp_output = '
<div class="panel panel-default panel-condensed table-responsive">
<table class="table table-hover table-condensed table-striped">
  <thead>
    <tr class="info">
      <th>Summary</th>
      <th><a href="devices/">Devices</a></th>
      <th><a href="ports/">Ports</a></th>
';

if ($config['show_services']) {

    $temp_output .= '
      <th><a href="services/">Services</a></th>
';

}

$temp_output .= '
    </tr>
  </thead>
  <tbody>
    <tr class="active">
      <th><span class="green">Up</span></th>
      <td><a href="devices/format=list_detail/state=up/"><span class="green">'. $devices['up'] .'</span></a></td>
      <td><a href="ports/format=list_detail/state=up/"><span class="green">'. $ports['up'] .'</span></a></td>
';
if ($config['show_services']) {

    $temp_output .= '
      <td><a href="services/view=details/state=up/"><span class="green">'. $services['up'] .'</span></a></td>
';

}

$temp_output .= '
    </tr>
    <tr class="active">
      <th><span class="red">Down</span></th>
      <td><a href="devices/format=list_detail/state=down/"><span class="red">'. $devices['down'] .'</span></a></td>
      <td><a href="ports/format=list_detail/state=down/"><span class="red">'. $ports['down'] .'</span></a></td>
';

if ($config['show_services']) {

    $temp_output .= '
      <td><a href="services/view=details/state=down/"><span class="red">'. $services['down'] .'</span></a></td>
';

}

$temp_output .= '
    </tr>
    <tr class="active">
      <th><span class="grey">Ignored</span></th>
      <td><a href="devices/format=list_detail/ignore=1/"><span class="grey">'. $devices['ignored'] .'</span></a></td>
      <td><a href="ports/format=list_detail/ignore=1/"><span class="grey">'. $ports['ignored'] .'</span></a></td>
';

if ($config['show_services']) {

    $temp_output .= '
      <td><a href="services/view=details/ignore=1/"><span class="grey">'. $services['ignored'] .'</span></a></td>
';
}

$temp_output .= '
    </tr>
    <tr class="active">
      <th><span class="black">Disabled/Shutdown</span></th>
      <td><a href="devices/format=list_detail/disabled=1/"><span class="black">'. $devices['disabled'] .'</span></a></td>
      <td><a href="ports/format=list_detail/state=admindown/"><span class="black">'. $ports['shutdown'] .'</span></a></td>
';

if ($config['show_services']) {

    $temp_output .= '
      <td><a href="services/view=details/disabled=1/"><span class="black">'. $services['disabled'] .'</span></a></td>
';

}

if ($config['summary_errors']) {
    $temp_output .= '
    </tr>
    <tr class="active">
      <th><span class="black">Errored</span></th>
      <td>-</td>
      <td><a href="ports/format=list_detail/errors=1/"><span class="black"> '.$ports['errored'].'</span></a></td>
';
    if ($config['show_services']) {
        $temp_output .= '
      <td>-</td>
';
    }
}

$temp_output .= '
    </tr>
    <tr class="active">
      <th><span class="grey">Total</span></th>
      <td><a href="devices/"><span>'. $devices['count'] .'</span></a></td>
      <td><a href="ports/"><span>'. $ports['count'] .'</span></a></td>
';

if ($config['show_services']) {

    $temp_output .= '
      <td><a href="services/"><span>'. $services['count'] .'</span></a></td>
';

}

$temp_output .= '
    </tr>
  </tbody>
</table>
</div>
';

unset($common_output);
$common_output[] = $temp_output;
