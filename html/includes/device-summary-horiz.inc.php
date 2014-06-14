<?php
include_once("includes/object-cache.inc.php");
?>
<div class="panel panel-default panel-condensed">
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
      <td><a href="devices/"><span><?php echo($devices['count']) ?></span></a></td>
      <td><a href="devices/state=up/format=list_detail/"><span class="green"> <?php echo($devices['up']) ?> up</span></a></td>
      <td><a href="devices/state=down/format=list_detail/"><span class="red"> <?php echo($devices['down']) ?> down</span></a></td>
      <td><a href="devices/ignore=1/format=list_detail/"><span class="grey"> <?php echo($devices['ignored']) ?> ignored </span></a></td>
      <td><a href="devices/disabled=1/format=list_detail/"><span class="black"> <?php echo($devices['disabled']) ?> disabled</span></a></td>
    </tr>
    <tr class="active">
      <td><a href="ports/">Ports</a></td>
      <td><a href="ports/"><span><?php echo($ports['count']) ?></span></a></td>
      <td><a href="ports/format=list_detail/state=up/"><span class="green"> <?php echo($ports['up']) ?> up </span></a></td>
      <td><a href="ports/format=list_detail/state=down/"><span class="red"> <?php echo($ports['down']) ?> down </span></a></td>
      <td><a href="ports/format=list_detail/ignore=1/"><span class="grey"> <?php echo($ports['ignored']) ?> ignored </span></a></td>
      <td><a href="ports/format=list_detail/state=admindown/"><span class="black"> <?php echo($ports['shutdown']) ?> shutdown</span></a></td>
    </tr>
<?php if ($config['show_services']) { ?>
    <tr class="active">
      <td><a href="services/">Services</a></td>
      <td><a href="services/"><span><?php echo($services['count']) ?></span></a></td>
      <td><a href="services/state=up/view=details/"><span class="green"><?php echo($services['up']) ?> up</span></a></td>
      <td><a href="services/state=down/view=details/"><span class="red"> <?php echo($services['down']) ?> down</span></a></td>
      <td><a href="services/ignore=1/view=details/"><span class="grey"> <?php echo($services['ignored']) ?> ignored</span></a></td>
      <td><a href="services/disabled=1/view=details/"><span class="black"> <?php echo($services['disabled']) ?> disabled</span></a></td>
    </tr>
<?php } ?>
  </tbody>
</table>
</div>
