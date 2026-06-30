<?php

/**
 * LibreNMS Application: StorCLI RAID Monitor (storraid)
 *
 * Reads the JSON payload stored in $app->data by the poller.
 */

use LibreNMS\Util\Url;

// ── Severity helpers ──────────────────────────────────────────────────────────
function storraid_severity_badge(int $sev): string
{
    return match ($sev) {
        0 => '<span class="label label-success">OK</span>',
        1 => '<span class="label label-warning">WARN</span>',
        default => '<span class="label label-danger">CRIT</span>',
    };
}

function storraid_severity_icon(int $sev): string
{
    return match ($sev) {
        0 => '<i class="fa fa-check-circle text-success"></i>',
        1 => '<i class="fa fa-exclamation-triangle text-warning"></i>',
        default => '<i class="fa fa-times-circle text-danger"></i>',
    };
}

function storraid_temp(mixed $temp): string
{
    return ($temp !== null && $temp !== 'N/A') ? ((int) $temp . ' °C') : 'N/A';
}

// ── Read data stored by the poller ────────────────────────────────────────────
$data = $app->data;

if (empty($data) || ! is_array($data)) {
    echo '<div class="alert alert-warning">'
        . '<strong>No data yet.</strong> '
        . 'The poller has not collected storraid data yet. '
        . 'Run: <code>sudo -u librenms /opt/librenms/lnms device:poll HOSTNAME --module applications</code>'
        . '</div>';

    return;
}

if (! empty($data['error'])) {
    echo '<div class="alert alert-danger">Agent error: '
        . htmlspecialchars((string) $data['error'])
        . '</div>';

    return;
}

$controllers = $data['controllers'] ?? [];
$virtual_disks = $data['virtual_disks'] ?? [];
$physical_disks = $data['physical_disks'] ?? [];
$summary = $data['summary'] ?? [];
$ts_raw = $data['timestamp'] ?? null;
$overall_sev = (int) ($summary['overall_severity'] ?? 0);

// Format the agent timestamp using LibreNMS's Carbon instance so it respects
// the user's timezone and date format preferences.
if ($ts_raw !== null) {
    try {
        $ts_formatted = \Carbon\Carbon::parse($ts_raw)
            ->setTimezone(config('app.timezone', 'UTC'))
            ->toDateTimeString();
    } catch (\Exception) {
        $ts_formatted = $ts_raw;
    }
} else {
    $ts_formatted = 'N/A';
}

// ── Overall status banner ─────────────────────────────────────────────────────
$banner_class = match ($overall_sev) {
    0 => 'alert-success',
    1 => 'alert-warning',
    default => 'alert-danger',
};
$banner_text = match ($overall_sev) {
    0 => 'All RAID components are healthy.',
    1 => 'Warning: One or more components require attention.',
    default => 'Critical: RAID degradation or disk failure detected!',
};

echo "<div class='alert {$banner_class}'><strong>" . htmlspecialchars($banner_text) . '</strong>'
    . ' &nbsp; Last polled: ' . htmlspecialchars((string) $ts_formatted) . '</div>';

// ── Summary cards ─────────────────────────────────────────────────────────────
echo "
<div class='row' style='margin-bottom:20px'>
  <div class='col-md-4'>
    <div class='panel panel-default'>
      <div class='panel-heading'><h4>Controllers</h4></div>
      <div class='panel-body text-center'>
        <span class='label label-success'>&check; {$summary['ctrl_ok']} OK</span>
        <span class='label label-warning'>&#9651; {$summary['ctrl_warn']} Warn</span>
        <span class='label label-danger'>&#10005; {$summary['ctrl_crit']} Crit</span>
      </div>
    </div>
  </div>
  <div class='col-md-4'>
    <div class='panel panel-default'>
      <div class='panel-heading'><h4>Virtual Disks</h4></div>
      <div class='panel-body text-center'>
        <span class='label label-success'>&check; {$summary['vd_ok']} OK</span>
        <span class='label label-warning'>&#9651; {$summary['vd_warn']} Warn</span>
        <span class='label label-danger'>&#10005; {$summary['vd_crit']} Crit</span>
      </div>
    </div>
  </div>
  <div class='col-md-4'>
    <div class='panel panel-default'>
      <div class='panel-heading'><h4>Physical Disks</h4></div>
      <div class='panel-body text-center'>
        <span class='label label-success'>&check; {$summary['pd_ok']} OK</span>
        <span class='label label-warning'>&#9651; {$summary['pd_warn']} Warn</span>
        <span class='label label-danger'>&#10005; {$summary['pd_crit']} Crit</span>
      </div>
    </div>
  </div>
</div>";

// ── Controllers ───────────────────────────────────────────────────────────────
echo "<h3>Controllers</h3><div class='row'>";
foreach ($controllers as $ctrl) {
    if (isset($ctrl['error'])) {
        echo "<div class='alert alert-danger'>Controller error: " . htmlspecialchars((string) $ctrl['error']) . '</div>';
        continue;
    }
    $sev = (int) ($ctrl['severity'] ?? 0);
    $icon = storraid_severity_icon($sev);
    $badge = storraid_severity_badge($sev);
    $bbu = $ctrl['bbu'] ?? null;
    $bbu_html = '';
    if ($bbu) {
        $bbu_badge = storraid_severity_badge((int) ($bbu['severity'] ?? 1));
        $bbu_html = "
        <tr>
          <th>BBU / CacheVault</th>
          <td>{$bbu['type']} &nbsp; {$bbu_badge} &nbsp; State: <strong>"
            . htmlspecialchars((string) $bbu['state']) . '</strong>'
            . ($bbu['temperature'] !== 'N/A' ? ' &nbsp; Temp: ' . htmlspecialchars((string) $bbu['temperature']) : '')
            . ($bbu['charge_pct'] !== 'N/A' ? ' &nbsp; Charge: ' . htmlspecialchars((string) $bbu['charge_pct']) : '')
            . '</td>
        </tr>';
    }
    echo "
    <div class='col-md-6'>
    <div class='panel panel-default'>
      <div class='panel-heading'>
        {$icon} <strong>Controller {$ctrl['id']}</strong> &mdash; "
        . htmlspecialchars((string) $ctrl['model']) . " &nbsp; {$badge}
      </div>
      <div class='panel-body' style='padding:0'>
        <table class='table table-condensed' style='table-layout:fixed;width:100%;margin-bottom:0'>
          <colgroup><col style='width:130px'><col></colgroup>
          <tr><th>Serial</th><td>" . htmlspecialchars((string) $ctrl['serial']) . '</td></tr>
          <tr><th>Firmware</th><td>' . htmlspecialchars((string) $ctrl['firmware']) . '</td></tr>
          <tr><th>Memory</th><td>' . htmlspecialchars((string) $ctrl['memory_mb']) . '</td></tr>
          <tr><th>Temperature</th><td>' . storraid_temp($ctrl['temperature']) . '</td></tr>';

    echo "
          <tr><th>VDs / PDs</th><td>{$ctrl['vd_count']} VDs &nbsp;/&nbsp; {$ctrl['pd_count']} PDs</td></tr>
          {$bbu_html}
        </table>
      </div>
    </div>
    </div>";
}
echo '</div>'; // end .row controllers

// ── Virtual Disks ─────────────────────────────────────────────────────────────
echo "<h3>Virtual Disks (RAID Arrays)</h3>
<table class='table table-striped table-hover table-condensed'>
  <thead>
    <tr>
      <th>Status</th><th>Ctrl</th><th>VD</th><th>Name</th>
      <th>RAID</th><th>Size</th><th>State</th><th>Access</th>
      <th>Cache</th><th>Consist</th><th>Progress</th>
    </tr>
  </thead>
  <tbody>";

foreach ($virtual_disks as $vd) {
    $sev = (int) ($vd['severity'] ?? 0);
    $icon = storraid_severity_icon($sev);
    $state = htmlspecialchars($vd['state'] ?? 'Unknown');
    $prog = ($vd['progress_pct'] !== null)
        ? "<div class='progress' style='margin:0;min-width:60px'>"
          . "<div class='progress-bar progress-bar-info' style='width:{$vd['progress_pct']}%'>"
          . "{$vd['progress_pct']}%</div></div>"
        : '&mdash;';
    $row_class = match ($sev) {
        0 => '', 1 => 'warning', default => 'danger'
    };

    echo "
    <tr class='{$row_class}'>
      <td>{$icon}</td>
      <td>c{$vd['controller']}</td>
      <td>" . htmlspecialchars((string) $vd['id']) . '</td>
      <td>' . htmlspecialchars((string) $vd['name']) . '</td>
      <td><strong>' . htmlspecialchars((string) $vd['raid_level']) . '</strong></td>
      <td>' . htmlspecialchars((string) $vd['size']) . "</td>
      <td><strong>{$state}</strong></td>
      <td>" . htmlspecialchars((string) $vd['access']) . '</td>
      <td>' . htmlspecialchars((string) $vd['cache']) . '</td>
      <td>' . htmlspecialchars((string) $vd['consist']) . "</td>
      <td>{$prog}</td>
    </tr>";
}
echo '</tbody></table>';

// ── Physical Disks ────────────────────────────────────────────────────────────
echo "<h3>Physical Disks</h3>
<table class='table table-striped table-hover table-condensed'>
  <thead>
    <tr>
      <th>Status</th><th>Ctrl</th><th>EID:Slot</th><th>VD</th>
      <th>Model</th><th>Serial</th><th>Firmware</th><th>Size</th><th>Type</th><th>Intf</th>
      <th>State</th><th>Temp</th><th>Media Err</th><th>Other Err</th>
      <th>Pred Fail</th><th>SMART</th>
    </tr>
  </thead>
  <tbody>";

foreach ($physical_disks as $pd) {
    $sev = (int) ($pd['severity'] ?? 0);
    $icon = storraid_severity_icon($sev);
    $smart = $pd['smart_alert']
        ? '<span class="label label-danger">ALERT</span>'
        : '<span class="label label-success">OK</span>';
    $merr_class = ((int) $pd['media_errors'] > 0) ? 'text-danger' : '';
    $oerr_class = ((int) $pd['other_errors'] > 5) ? 'text-warning' : '';
    $row_class = match ($sev) {
        0 => '', 1 => 'warning', default => 'danger'
    };

    $pd_id = 'c' . $pd['controller'] . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $pd['eid_slot']);
    $graph_pop = [
        'type' => 'application_storraid_pd_disk',
        'id' => $app->app_id,
        'disk' => $pd_id,
        'height' => '150',
        'width' => '400',
        'to' => \App\Facades\LibrenmsConfig::get('time.now'),
        'scale_min' => '0',
    ];

    $model_link = Url::overlibLink(
        '#',
        htmlspecialchars((string) $pd['model']),
        Url::graphTag($graph_pop)
    );

    echo "
    <tr class='{$row_class}'>
      <td>{$icon}</td>
      <td>c{$pd['controller']}</td>
      <td>" . htmlspecialchars((string) $pd['eid_slot']) . '</td>
      <td>' . htmlspecialchars((string) $pd['vd']) . "</td>
      <td>{$model_link}</td>
      <td>" . htmlspecialchars($pd['serial'] ?? 'N/A') . '</td>
      <td>' . htmlspecialchars(trim($pd['firmware'] ?? 'N/A')) . '</td>
      <td>' . htmlspecialchars((string) $pd['size']) . '</td>
      <td>' . htmlspecialchars((string) $pd['media_type']) . '</td>
      <td>' . htmlspecialchars((string) $pd['interface']) . '</td>
      <td><strong>' . htmlspecialchars((string) $pd['state']) . '</strong></td>
      <td>' . storraid_temp($pd['temperature']) . "</td>
      <td class='{$merr_class}'>" . (int) $pd['media_errors'] . "</td>
      <td class='{$oerr_class}'>" . (int) $pd['other_errors'] . '</td>
      <td>' . htmlspecialchars((string) $pd['pred_failure']) . "</td>
      <td>{$smart}</td>
    </tr>";
}
echo '</tbody></table>';

// ── RRD Graphs ────────────────────────────────────────────────────────────────
echo '<h3>Graphs</h3>';

// Summary graph
$graph_array = [
    'height' => '100',
    'width' => '215',
    'to' => \App\Facades\LibrenmsConfig::get('time.now'),
    'id' => $app->app_id,
    'type' => 'application_storraid_summary',
    'scale_min' => '0',
];
echo "<div class='panel panel-default'>
  <div class='panel-heading'><h3 class='panel-title'>Component Status (Controllers / VDs / PDs)</h3></div>
  <div class='panel-body'><div class='row'>";
include 'includes/html/print-graphrow.inc.php';
echo '</div></div></div>';

// Controller + disk temperature graph
if (! empty($controllers)) {
    $graph_array = [
        'height' => '100',
        'width' => '215',
        'to' => \App\Facades\LibrenmsConfig::get('time.now'),
        'id' => $app->app_id,
        'type' => 'application_storraid_temp',
        'scale_min' => '0',
    ];
    echo "<div class='panel panel-default'>
      <div class='panel-heading'><h3 class='panel-title'>Temperatures (Controllers &amp; Disks)</h3></div>
      <div class='panel-body'><div class='row'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div></div>';
}

// Per-disk error graphs
if (! empty($physical_disks)) {
    echo '<h3>Physical Disk Errors</h3>';
    foreach ($physical_disks as $pd) {
        $pd_id = 'c' . $pd['controller'] . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $pd['eid_slot']);
        $pd_label = 'c' . $pd['controller'] . ' / ' . htmlspecialchars((string) $pd['eid_slot'])
                  . ' &mdash; ' . htmlspecialchars((string) $pd['model'])
                  . ' (' . htmlspecialchars((string) $pd['size']) . ')';

        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app->app_id,
            'disk' => $pd_id,
            'type' => 'application_storraid_pd_disk',
            'scale_min' => '0',
        ];

        echo "<div class='panel panel-default'>
          <div class='panel-heading'><h3 class='panel-title'>{$pd_label}</h3></div>
          <div class='panel-body'><div class='row'>";
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
}
