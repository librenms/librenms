<?php

require_once __DIR__ . '/alteonos_common.inc.php';

$rows = alteonos_loadbalancer_fetch($device, 'alteonos_real_groups');

$groups = [];
foreach ($rows as $row) {
    $index = $row['sensor_index'] ?? '';
    $parts = explode('.', (string) $index);
    $groupId = $parts[0] ?? null;
    $memberId = $parts[1] ?? null;

    if ($groupId === null || $memberId === null) {
        continue;
    }

    $sensorDescr = (string) ($row['sensor_descr'] ?? '');
    $ip = null;
    if ($sensorDescr !== '' && preg_match('/\(([^)]+)\)\s*$/', $sensorDescr, $match)) {
        $ip = $match[1];
    }

    $groups[$groupId]['heading'] = $groups[$groupId]['heading']
        ?? sprintf('Real Server Group %s', $groupId);

    $stateDescrRaw = trim((string) ($row['state_descr'] ?? ''));
    $stateDescr = $stateDescrRaw !== '' ? $stateDescrRaw : null;
    $stateGeneric = isset($row['state_generic_value']) ? (int) $row['state_generic_value'] : null;
    $needsFallback = $stateDescr === null || strtolower($stateDescr) === 'unknown';
    if ($needsFallback) {
        if ($fallback = alteonos_state_lookup((string) ($row['sensor_type'] ?? ''), $row['sensor_current'] ?? null)) {
            $stateDescr = $fallback['descr'];
            $stateGeneric = $fallback['generic'];
        }
    }

    $groups[$groupId]['members'][] = [
        'member' => $memberId,
        'ip' => $ip,
        'sensor_id' => $row['sensor_id'] ?? null,
        'state_descr' => $stateDescr ?? __('Unknown'),
        'state_generic' => $stateGeneric,
        'value' => $row['sensor_current'] ?? null,
        'updated' => $row['lastupdate'] ?? null,
    ];
}

ksort($groups, SORT_NATURAL);

if (empty($groups)) {
    echo '<div class="alert alert-info" role="alert">' . htmlspecialchars(__('No data available.'), ENT_QUOTES, 'UTF-8') . '</div>';

    return;
}

foreach ($groups as $group) {
    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading"><h3 class="panel-title">' . htmlspecialchars($group['heading'], ENT_QUOTES, 'UTF-8') . '</h3></div>';
    echo '<div class="panel-body">';

    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-condensed">';
    echo '<thead><tr>';
    echo '<th>' . htmlspecialchars(__('Member'), ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th>' . htmlspecialchars(__('IP Address'), ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th>' . htmlspecialchars(__('State'), ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th>' . htmlspecialchars(__('Value'), ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th>' . htmlspecialchars(__('Last Updated'), ENT_QUOTES, 'UTF-8') . '</th>';
    echo '</tr></thead><tbody>';

    foreach ($group['members'] as $member) {
        $stateClass = alteonos_state_class($member['state_generic']);
        $stateText = htmlspecialchars((string) $member['state_descr'], ENT_QUOTES, 'UTF-8');
        $value = $member['value'] !== null ? htmlspecialchars((string) $member['value'], ENT_QUOTES, 'UTF-8') : '-';
        $updated = $member['updated'] ? htmlspecialchars((string) $member['updated'], ENT_QUOTES, 'UTF-8') : '-';

        echo '<tr>';
        echo '<td>' . htmlspecialchars('Real Server ' . $member['member'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . ($member['ip'] !== null ? htmlspecialchars($member['ip'], ENT_QUOTES, 'UTF-8') : '-') . '</td>';
        echo '<td><span class="' . $stateClass . '">' . $stateText . '</span></td>';
        echo '<td>' . $value . '</td>';
        echo '<td class="text-nowrap">' . $updated . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
    echo '</div></div>';
}
