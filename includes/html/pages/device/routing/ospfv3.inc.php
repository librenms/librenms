<?php

use Illuminate\Support\Facades\Blade;

$i = 0;

echo '
<div>
  <div class="panel panel-default">
    <div class="panel-body">
      <table class="table table-condensed" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Router ID</th>
            <th>Status</th>
            <th>ABR</th>
            <th>ASBR</th>
            <th>Areas</th>
            <th>Ports(Enabled)</th>
            <th>Neighbours</th>
          </tr>
        </thead>';

$instances = DeviceCache::getPrimary()->ospfv3Instances()
    ->with([
        'areas.ospfv3Ports',
        'nbrs.port',
        'ospfv3Ports.port',
    ])->get();

foreach ($instances as $instance) {
    $i++;

    $port_count_enabled = $instance->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count();

    $status_color = $instance->ospfv3AdminStatus == 'enabled' ? 'success' : 'default';
    $abr_status_color = $instance->ospfv3AreaBdrRtrStatus == 'true' ? 'success' : 'default';
    $asbr_status_color = $instance->ospfv3ASBdrRtrStatus == 'true' ? 'success' : 'default';

    echo '
        <tbody>
          <tr data-toggle="collapse" data-target="#ospf-panel' . $i . '" class="accordion-toggle">
            <td><button id="ospf-panel_button' . $i . '" class="btn btn-default btn-xs"><span id="ospf-panel_span' . $i . '" class="fa fa-plus"></span></button></td>
            <td>' . $instance->router_id . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance->ospfv3AdminStatus . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance->ospfv3AreaBdrRtrStatus . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance->ospfv3ASBdrRtrStatus . '</span></td>
            <td>' . $instance->areas->count() . '</td>
            <td>' . $instance->ospfv3Ports->count() . '(' . $port_count_enabled . ')</td>
            <td>' . $instance->nbrs->count() . '</td>
          </tr>
          <script type="text/javascript">
          $("#ospf-panel_button' . $i . '").on("click", function(){
              $("#ospf-panel_span' . $i . '").toggleClass("fa-minus");
          });
          </script>
          <tr>
            <td colspan="12" class="hiddenRow">
            <div class="accordian-body collapse" id="ospf-panel' . $i . '">
                <br>
                <div class="col-xs-4">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <h4><span class="label label-primary">Areas</span></h4>
                        <tr>
                          <th>Area ID</th>
                          <th>Ports(Enabled)</th>
                          <th>LSAs</th>
                          <th>Status</th>
                        </tr>
                      </thead>';
    foreach ($instance->areas as $area) {
        $area_port_count = $area->ospfv3Ports->count();
        $area_port_count_enabled = $area->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count();

        echo '
                      <tbody>
                        <tr>
                          <td>' . long2ip($area->ospfv3AreaId) . '</td>
                          <td>' . $area_port_count . '(' . $area_port_count_enabled . ')</td>
                          <td>' . $area->ospfv3AreaScopeLsaCount . '</td>
                          <td><span class="label label-' . $status_color . '">' . $instance->ospfv3AdminStatus . '</span></td>
                        </tr>
                      </tbody>';
    }
    echo '
                    </table>
                  </div>
                </div>
                <div class="col-xs-4">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <h4><span class="label label-primary">Ports</span></h4>
                        <tr>
                          <th>Port</th>
                          <th>Port Type</th>
                          <th>Port State</th>
                          <th>Cost</th>
                          <th>Status</th>
                          <th>Area ID</th>
                        </tr>
                      </thead>
                  </div>';

    foreach ($instance->ospfv3Ports as $ospfPort) {
        $port_status_color = $ospfPort->ospfv3IfAdminStatus == 'enabled' ? 'success' : 'default';

        echo '
                  <tbody>
                    <tr>
                      <td>' . ($ospfPort->port ? Blade::render('<x-port-link :port="$port"/>', ['port' => $ospfPort->port]) : '') . '</td>
                      <td>' . $ospfPort->ospfv3IfType . '</td>
                      <td>' . $ospfPort->ospfv3IfState . '</td>
                      <td>' . $ospfPort->ospfv3IfMetricValue . '</td>
                      <td><span class="label label-' . $port_status_color . '">' . $ospfPort->ospfv3IfAdminStatus . '</span></td>
                      <td>' . long2ip($ospfPort->ospfv3IfAreaId) . '</td>
                    </tr>
                  </tbody>';
    }
    echo '
                  </table>
                </div>
                </div>
                <div class="col-xs-4">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <h4><span class="label label-primary">Neighbours</span></h4>
                        <tr>
                          <th>Router ID</th>
                          <th>Device</th>
                          <th>IP Address</th>
                          <th>Status</th>
                        </tr>
                      </thead>';
    foreach ($instance->nbrs as $nbr) {
        $rtr_id = 'unknown';
        if ($nbr->port) {
            $rtr_id = Blade::render('<x-device-link :device="$device" tab="routing" section="ospfv3"/>', ['device' => $nbr->port->device_id]);
        }

        $ospfnbr_status_color = 'default';
        if ($nbr->ospfv3NbrState == 'full') {
            $ospfnbr_status_color = 'success';
        } elseif ($nbr->ospfv3NbrState == 'down') {
            $ospfnbr_status_color = 'danger';
        }

        echo '
                    <tbody>
                      <tr>
                        <td>' . $nbr->router_id . '</td>
                        <td>' . $rtr_id . '</td>
                        <td>' . $nbr->ospfv3NbrAddress . '</td>
                        <td><span class="label label-' . $ospfnbr_status_color . '">' . $nbr->ospfv3NbrState . '</span></td>
                      </tr>
                    </tbody>';
    }
    echo '
                    </table>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>';
}
echo '
      </table>
    </div>
  </div>
</div>';
