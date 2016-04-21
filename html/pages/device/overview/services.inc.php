<?php

if ($services['total']) {
    // Build the string.
    foreach (service_get ($device['device_id']) as $data) {
        if ($data['service_status'] == '1') {
            // Ok
            $status = 'green';
        } elseif ($data['service_status'] == '0') {
            // Critical
            $status = 'red';
        } elseif ($data['service_status'] == '2') {
            // Warning
            $status = 'red';
        } else {
            // Unknown
            $status = 'grey';
        }
        $string .= $break . '<a class=' . $status . '>' . strtolower ($data['service_type']) . '</a>';
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
                        <td title="Total"><img src='images/16/cog.png'> <?=$services['total']?></td>
                        <td title="Status - Ok"><img src='images/16/cog_add.png'> <?=$services[1]?></td>
                        <td title="Status - Critical"><img src='images/16/cog_delete.png'> <?=$services[0]?></td>
                        <td title="Status - Unknown"><img src='images/16/cog_error.png'> <?=$services[2]?></td>
                    </tr>
                    <tr>
                        <td colspan='4'><?=$string?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php
}