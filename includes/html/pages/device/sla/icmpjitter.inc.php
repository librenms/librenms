<div class="panel-heading">
    <h3 class="panel-title">Average Latency One Way</h3>
</div>
<div class="panel-body">
    <?php
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_sla_icmpjitter_latency';
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    ?>
</div>

<div class="panel-heading">
    <h3 class="panel-title">Average Jitter</h3>
</div>
<div class="panel-body">
    <?php
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_sla_icmpjitter_jitter';
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    ?>
</div>

<div class="panel-heading">
    <h3 class="panel-title">Packet Out of Sequence</h3>
</div>
<div class="panel-body">
    <?php
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_sla_icmpjitter_oos';
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    ?>
</div>

<div class="panel-heading">
    <h3 class="panel-title">Lost Packets (Loss, Skipped, Late Arrival)</h3>
</div>
<div class="panel-body">
    <?php
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_sla_icmpjitter_lost';
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    ?>
</div>

<div class="panel-heading">
    <h3 class="panel-title">Inter-Arrival Jitter (RFC 1889)</h3>
</div>
<div class="panel-body">
    <?php
    $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_sla_icmpjitter_iajitter';
    $graph_array['id'] = $vars['id'];
    require 'includes/html/print-graphrow.inc.php';
    ?>
</div>
