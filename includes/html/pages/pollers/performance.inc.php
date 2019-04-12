<?php

$pagetitle[] = 'Poll Performance';


?>
<br />
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Total Poller Time</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [
            'type' => 'global_poller_perf',
            'legend' => 'yes',
            'height' => 100,
        ];
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Total Poller Time Per Module</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [
            'type' => 'global_poller_modules_perf',
            'legend' => 'yes',
            'height' => 100,
        ];
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>
