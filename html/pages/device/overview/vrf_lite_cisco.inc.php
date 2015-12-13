<?php
    $device=device_by_id_cache($device['device_id']);
    
if (!empty($device['vrf_lite_cisco'])&&empty($vars['vrf-lite'])){
    echo('<div class="container-fluid">');
    echo('<div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-condensed">
                    <div class="panel-heading">
                        <strong>VRF lite</strong>
                    </div>
                    <table class="table table-hover table-condensed table-striped">');
    echo('          <tr><td colspan="4">');
    
    $uniqueVrf=array();
    foreach ($device['vrf_lite_cisco'] as $vrf) {
        $uniqueVrf[$vrf['vrf_name']]=$vrf['vrf_name'];
    }
    sort($uniqueVrf);
    
    foreach ($uniqueVrf as $vrf) {
        echo '<div class="col-md-4">'.generate_link($vrf, array('page'=>$vars['page'],'device'=>$vars['device'],'vrf-lite'=>$vrf)).'</div>';
    }
    unset($uniqueVrf);
    
    echo(" </td>");
    echo("</tr>");
    echo("</table>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
}
