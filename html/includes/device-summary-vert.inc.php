<?php
include_once("includes/object-cache.inc.php");
?>
<div class="panel panel-default panel-condensed table-responsive">
<table class="table table-hover table-condensed table-striped">
  <thead>
    <tr class="info">
      <th>Summary</th>
      <th><a href="devices/">Devices</a></th>
      <th><a href="ports/">Ports</a></th>
<?php if ($config['show_services']) { ?>
      <th><a href="services/">Services</a></th>
<?php } ?>
    </tr>
  </thead>
  <tbody>
    <tr class="active">
      <th><span class="green">Up</span></th>
      <td><a href="devices/format=list_detail/state=up/"><span class="green"><?php echo($devices['up']) ?></span></a></td>
      <td><a href="ports/format=list_detail/state=up/"><span class="green"><?php echo($ports['up']) ?></span></a></td>
<?php if ($config['show_services']) { ?>
      <td><a href="services/view=details/state=up/"><span class="green"><?php echo($services['up']) ?></span></a></td>
<?php } ?>
    </tr>
    <tr class="active">
      <th><span class="red">Down</span></th>
      <td><a href="devices/format=list_detail/state=down/"><span class="red"><?php echo($devices['down']) ?></span></a></td>
      <td><a href="ports/format=list_detail/state=down/"><span class="red"><?php echo($ports['down']) ?></span></a></td>
<?php if ($config['show_services']) { ?>
      <td><a href="services/view=details/state=down/"><span class="red"><?php echo($services['down']) ?></span></a></td>
<?php } ?>
    </tr>
    <tr class="active">
      <th><span class="grey">Ignored</span></th>
      <td><a href="devices/format=list_detail/ignore=1/"><span class="grey"><?php echo($devices['ignored']) ?></span></a></td>
      <td><a href="ports/format=list_detail/ignore=1/"><span class="grey"><?php echo($ports['ignored']) ?></span></a></td>
<?php if ($config['show_services']) { ?>
      <td><a href="services/view=details/ignore=1/"><span class="grey"><?php echo($services['ignored']) ?></span></a></td>
<?php } ?>
    </tr>
    <tr class="active">
      <th><span class="black">Disabled/Shutdown</span></th>
      <td><a href="devices/format=list_detail/disabled=1/"><span class="black"><?php echo($devices['disabled']) ?></span></a></td>
      <td><a href="ports/format=list_detail/state=admindown/"><span class="black"><?php echo($ports['shutdown']) ?></span></a></td>
<?php if ($config['show_services']) { ?>
      <td><a href="services/view=details/disabled=1/"><span class="black"><?php echo($services['disabled']) ?></span></a></td>
<?php } ?>
    </tr>
    <tr class="active">
      <th>Total</th>
      <td><a href="devices/"><span><?php echo($devices['count']) ?></span></a></td>
      <td><a href="ports/"><span><?php echo($ports['count']) ?></span></a></td>
<?php if ($config['show_services']) { ?>
      <td><a href="services/"><span><?php echo($services['count']) ?></span></a></td>
<?php } ?>
    </tr>
  </tbody>
</table>
</div>
