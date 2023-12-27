<?php

use App\Models\IsisAdjacency;

if (! Auth::user()->hasGlobalRead()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    $link_array = [
        'page' => 'routing',
        'protocol' => 'isis',
    ];

    print_optionbar_start('', '');

    echo '<span style="font-weight: bold;">Adjacencies</span> &#187; ';

    if (! $vars['state']) {
        $vars['state'] = 'all';
    }

    if ($vars['state'] == 'all') {
        $filter = ['up', 'down'];
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('All', $vars, ['state' => 'all']);
    if ($vars['state'] == 'all') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['state'] == 'up') {
        $filter = ['up'];
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Up', $vars, ['state' => 'up']);
    if ($vars['state'] == 'up') {
        $filter = ['up'];
        echo '</span>';
    }

    echo ' | ';

    if ($vars['state'] == 'down') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Down', $vars, ['state' => 'down']);
    if ($vars['state'] == 'down') {
        $filter = ['down'];
        echo '</span>';
    }

    print_optionbar_end();

    echo '
  <div>
    <div class="panel panel-default">
      <div class="panel-body">
        <table class="table table-condensed table-hover" style="border-collapse:collapse;">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th>Local Device</th>
              <th>Local Interface</th>
              <th>Adjacent</th>
              <th>System ID</th>
              <th>Area</th>
              <th>System Type</th>
              <th>Admin</th>
              <th>State</th>
              <th>Last Uptime</th>
            </tr>
          </thead>';

    foreach (IsisAdjacency::whereIn('isisISAdjState', $filter)->with('port')->get() as $adj) {
        $device = device_by_id_cache($adj->device_id);
        if ($adj->isisISAdjState == 'up') {
            $color = 'green';
        } else {
            $color = 'red';
        }

        $interface_name = $adj->port->ifName;

        echo '
          <tbody>
          <tr>
              <td></td>
              <td>' . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'isis']) . '</td>
              <td>' . \LibreNMS\Util\Url::portLink($adj->port) . '</td>
              <td>' . $adj->isisISAdjIPAddrAddress . '</td>
              <td>' . $adj->isisISAdjNeighSysID . '</td>
              <td>' . $adj->isisISAdjAreaAddress . '</td>
              <td>' . $adj->isisISAdjNeighSysType . '</td>
              <td>' . $adj->isisCircAdminState . '</td>
              <td><strong><span style="color: ' . $color . ';">' . $adj->isisISAdjState . '</span></strong></td>
              <td>' . \LibreNMS\Util\Time::formatInterval($adj->isisISAdjLastUpTime) . '</td>
          </tr>
          </tbody>';
    }
    echo '</table>
      </div>
    </div>
  </div>';
}
