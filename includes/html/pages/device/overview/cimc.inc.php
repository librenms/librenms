<?php

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id'], ['type'=>'Cisco-CIMC']);

// We only care about our device id.
$components = $components[$device['device_id']];

if (count($components) > 0) {
    ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-condensed">
                    <div class="panel-heading">
                    <i class="fa fa-cogs fa-lg icon-theme" aria-hidden="true"></i> <strong>Hardware Components</strong>
                    </div>
                    <table class="table table-hover table-condensed table-striped">
    <?php
    foreach ($components as $component => $array) {
        if ($array['disabled'] == 1) {
            continue;
        } elseif ($array['status'] == 2) {
            $class = 'danger';
            $message = 'Alert';
        } else {
            $class = '';
            $message = 'Ok';
        } ?>
                    <tr class="<?php echo $class ?>">
                        <td><?php echo $array['string']?></td>
                    </tr>
        <?php
        // Display an additional row to show the error
        if ($array['status'] == 2) {
            ?>
                    <tr class="<?php echo $class ?>">
                        <td>Error: <?php echo nl2br($array['error'])?></td>
                    </tr>
            <?php
        }
    } ?>
                    </table>
                </div>
            </div>
        </div>
    <?php
}
