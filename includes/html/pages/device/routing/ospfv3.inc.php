<?php

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

$instances = DeviceCache::getPrimary()->ospfv3Instances()->with(['device' => function ($query) {
    return $query->withCount(['ospfv3Areas', 'ospfv3Ports', 'ospfv3Nbrs']);
}])->get();

foreach ($instances as $instance) {
    $i++;

    $port_count_enabled = DeviceCache::getPrimary()->ospfv3Ports()->where('ospfv3IfAdminStatus', 'enabled')->count();

    $status_color = $instance->ospfv3AdminStatus == 'enabled' ? 'success' : 'default';
    $abr_status_color = $instance->ospfv3AreaBdrRtrStatus == 'true' ? 'success' : 'default';
    $asbr_status_color = $instance->ospfv3ASBdrRtrStatus == 'true' ? 'success' : 'default';

    echo '
        <tbody>
          <tr data-toggle="collapse" data-target="#ospf-panel' . $i . '" class="accordion-toggle">
            <td><button id="ospf-panel_button' . $i . '" class="btn btn-default btn-xs"><span id="ospf-panel_span' . $i . '" class="fa fa-plus"></span></button></td>
            <td>' . $instance->ospfv3RouterId . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance->ospfv3AdminStatus . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance->ospfv3AreaBdrRtrStatus . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance->ospfv3ASBdrRtrStatus . '</span></td>
            <td>' . $instance->device->ospfv3_areas_count . '</td>
            <td>' . $instance->device->ospfv3_ports_count . '(' . $port_count_enabled . ')</td>
            <td>' . $instance->device->ospfv3_nbrs_count . '</td>
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
                          <th>Status</th>
                        </tr>
                      </thead>';
    foreach (DeviceCache::getPrimary()->ospfv3Areas as $area) {
        $area_port_count = DeviceCache::getPrimary()->ospfv3Ports()->where('ospfv3IfAreaId', $area->ospfv3AreaId)->count();
        $area_port_count_enabled = DeviceCache::getPrimary()->ospfv3Ports()->where('ospfv3IfAreaId', $area->ospfv3AreaId)->where('ospfv3IfAdminStatus', 'enabled')->count();

        echo '
                      <tbody>
                        <tr>
                          <td>' . (filter_var($area->ospfv3AreaId, FILTER_VALIDATE_IP) ? $area->ospfv3AreaId : long2ip($area->ospfv3AreaId)) . '</td>
                          <td>' . $area_port_count . '(' . $area_port_count_enabled . ')</td>
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
    // P.port_id does not match up with O.port_id, resulting in empty query.
    $ospfPorts = DeviceCache::getPrimary()->ospfv3Ports()->where('ospfv3IfAdminStatus', 'enabled')->with('port')->get();
    foreach ($ospfPorts as $ospfPort) {
        $port_status_color = $ospfPort->ospfv3IfAdminStatus == 'enabled' ? 'success' : 'default';

        echo '
                  <tbody>
                    <tr>
                      <td>' . \LibreNMS\Util\Url::portLink($ospfPort->port) . '</td>
                      <td>' . $ospfPort->ospfv3IfType . '</td>
                      <td>' . $ospfPort->ospfv3IfState . '</td>
                      <td>' . $ospfPort->ospfv3IfMetricValue . '</td>
                      <td><span class="label label-' . $port_status_color . '">' . $ospfPort->ospfv3IfAdminStatus . '</span></td>
                      <td>' . (filter_var($ospfPort->ospfv3IfAreaId, FILTER_VALIDATE_IP) ? $ospfPort->ospfv3IfAreaId : long2ip($ospfPort->ospfv3IfAreaId)) . '</td>
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
    foreach (DeviceCache::getPrimary()->ospfv3Nbrs as $nbr) {
        $port = PortCache::getByIp($nbr->ospfv3NbrRtrId);

        $rtr_id = 'unknown';
        if ($port) {
            $rtr_id = \LibreNMS\Util\Url::deviceLink(DeviceCache::get($port->device_id));
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
                        <td>' . $nbr->ospfv3NbrRtrId . '</td>
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
