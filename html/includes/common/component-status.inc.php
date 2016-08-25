<?php

$OBJCOMP = new LibreNMS\Component();

$common_output[] = '
<div>
    <table id="component-status" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="status" data-order="desc">Status</th>
                <th data-column-id="count">Count</th>
            </tr>
        </thead>
        <tbody>
';
foreach ($OBJCOMP->getComponentStatus() as $k => $v) {
    if ($k == 0) {
        $status = 'Ok';
        $color = 'green';
    } elseif ($k == 1) {
        $status = 'Warning';
        $color = 'grey';
    } else {
        $status = 'Critical';
        $color = 'red';
    }
    $common_output[] .= '
            <tr>
                <td><p class="text-left '.$color.'">'.$status.'</p></td>
                <td><p class="text-left '.$color.'">'.$v.'</p></td>
            </tr>
';
}
$common_output[] .= '
        </tbody>
    </table>
</div>
';
