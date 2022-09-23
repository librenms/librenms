<?php

use LibreNMS\Util\ObjectCache;

if (ObjectCache::serviceCounts(['total'], $device['device_id'])['total'] > 0) {
    $colors = collect(['green', 'yellow', 'red']);
    $output = \App\Models\Service::query()
        ->where('device_id', $device['device_id'])
        ->orderBy('service_type')
        ->get(['service_type', 'service_status', 'service_message'])
        ->map(function ($service) use ($colors) {
            $message = str_replace(' ', '&nbsp;', $service->service_message);
            $color = $colors->get($service->service_status, 'grey');
            $type = strtolower($service->service_type);

            return "<span title='$message' class='$color'>$type</span>";
        })->implode(', ');

    $services = ObjectCache::serviceCounts(['total', 'ok', 'warning', 'critical'], $device['device_id']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-condensed">
                    <div class="panel-heading">
                    <?php echo '<a href="device/device=' . $device['device_id'] . '/tab=services">'?><i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i> <strong>Services</strong><?php echo '</a>'?>
                    </div>
                    <table class="table table-hover table-condensed table-striped">
                        <tr>
                            <td title="Total"><i class="fa fa-cog" aria-hidden="true"></i> <?php echo $services['total']?></td>
                            <td title="Status - Ok"><i class="fa fa-cog" style="color:green" aria-hidden="true"></i> <?php echo $services['ok']?></td>
                            <td title="Status - Warning"><i class="fa fa-cog" style="color:orange" aria-hidden="true"></i> <?php echo $services['warning']?></td>
                            <td title="Status - Critical"><i class="fa fa-cog" style="color:red" aria-hidden="true"></i> <?php echo $services['critical']?></td>
                        </tr>
                        <tr>
                            <td colspan='4'><?php echo $output?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    <?php
}
