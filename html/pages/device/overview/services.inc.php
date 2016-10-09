<?php

if ($services['total']) {
    // Build the string.
    foreach (service_get($device['device_id']) as $data) {
        if ($data['service_status'] == '0') {
            // Ok
            $status = 'green';
        } elseif ($data['service_status'] == '1') {
            // Warning
            $status = 'red';
        } elseif ($data['service_status'] == '2') {
            // Critical
            $status = 'red';
        } else {
            // Unknown
            $status = 'grey';
        }
        $string .= $break . '<a class=' . $status . '>' . strtolower($data['service_type']) . '</a>';
        $break = ', ';
    }
    ?>
    <div class="container-fluid">
        <div class="row col-md-12">
            <div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <img src='images/16/cog.png'><strong> Services</strong>
                </div>
                <table class="table table-hover table-condensed table-striped">
                    <tr>
                        <td title="Total"><img src='images/16/cog.png'> <?php echo $services['total']?></td>
                        <td title="Status - Ok"><img src='images/16/cog_add.png'> <?php echo $services[0]?></td>
                        <td title="Status - Warning"><img src='images/16/cog_error.png'> <?php echo $services[1]?></td>
                        <td title="Status - Critical"><img src='images/16/cog_delete.png'> <?php echo $services[2]?></td>
                    </tr>
                    <tr>
                        <td colspan='4'><?php echo $string?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php
}
