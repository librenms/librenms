<?php

echo '<table class="table table-condensed table-striped table-hover">';
$stp_raw = dbFetchRow('SELECT * FROM `stp` WHERE `device_id` = ?', [$device['device_id']]);
$stp = [
    'Root bridge'                 => ($stp_raw['rootBridge'] == 1) ? 'Yes' : 'No',
    'Bridge address (MAC)'        => \LibreNMS\Util\Rewrite::readableMac($stp_raw['bridgeAddress']) . ' (' . \LibreNMS\Util\Rewrite::readableOUI($stp_raw['bridgeAddress']) . ')',
    'Protocol specification'      => $stp_raw['protocolSpecification'],
    'Priority (0-61440)'          => $stp_raw['priority'],
    'Time since topology change'  => \LibreNMS\Util\Time::formatInterval($stp_raw['timeSinceTopologyChange']),
    'Topology changes'            => $stp_raw['topChanges'],
    'Designated root (MAC)'       => \LibreNMS\Util\Rewrite::readableMac($stp_raw['designatedRoot']) . ' (' . \LibreNMS\Util\Rewrite::readableOUI($stp_raw['designatedRoot']) . ')',
    'Root cost'                   => $stp_raw['rootCost'],
    'Root port'                   => $stp_raw['rootPort'],
    'Max age (s)'                 => $stp_raw['maxAge'],
    'Hello time (s)'              => $stp_raw['helloTime'],
    'Hold time (s)'               => $stp_raw['holdTime'],
    'Forward delay (s)'           => $stp_raw['forwardDelay'],
    'Bridge max age (s)'          => $stp_raw['bridgeMaxAge'],
    'Bridge hello time (s)'       => $stp_raw['bridgeHelloTime'],
    'Bridge forward delay (s)'    => $stp_raw['bridgeForwardDelay'],
];
foreach (array_keys($stp) as $key) {
    echo '
      <tr>
        <td>' . $key . '</td>
        <td>' . $stp[$key] . '</td>
      </tr>
    ';
}
echo '</table>';
