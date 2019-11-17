<?php

use LibreNMS\Util\StringHelpers;

$app_id = \App\Models\Application::query()->where('device_id', $device['device_id'])->where('app_type', 'puppet-agent')->get('app_id')[0];

// show only if Puppet Agent Application discovered
if (count($app_id)) {
    $params = [];
    $sql = "SELECT `metric`, `value` FROM `application_metrics` WHERE `app_id` =" . $app_id['app_id'];
    $metrics = dbFetchKeyValue($sql, $params);

    ?><div class='row'>
          <div class='col-md-12'>
              <div class='panel panel-default panel-condensed device-overview'>
                  <div class='panel-heading'>
                      <a href="device/device=<?php echo $device['device_id']?>/tab=apps/app=puppet-agent/">
                          <i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i>
                          <strong>Puppet Agent</strong>
                      </a>
                  </div>
              <div class="panel-body">
    <?php
    echo '<div class="row">
      <div class="col-sm-4">Summary</div>
        <div class="col-sm-8">
          <table width=100%><tr>
            <td><span>Last run: '.$metrics['last_run_last_run'].'min</span></td>
            <td><span>Runtime: '.$metrics['time_total'].'s</span></td>
            <td><span>Resources: '.$metrics['resources_total'].'</span></td>
          </tr></table>
        </div>
        <div class="col-sm-4">Change Events</div>
        <div class="col-sm-8">
          <table width=100%><tr>
            <td><span ' . ($metrics['events_success']?'class="blue"':''). '>Success: '.$metrics['events_success'].'</span></td>
            <td><span ' . ($metrics['events_failure']?'class="red"':'').'>Failure: '.$metrics['events_failure'].'</span></td>
            <td><span>Total: '.$metrics['events_total'].'</span></td>
          </tr></table>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>';
}
