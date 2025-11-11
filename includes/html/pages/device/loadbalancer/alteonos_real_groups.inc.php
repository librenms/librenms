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

    $ip = null;
    if (! empty($row['sensor_descr']) && preg_match('/\(([^)]+)\)\s*$/', $row['sensor_descr'], $match)) {
        $ip = $match[1];
    }

    $groups[$groupId]['members'][] = [
        'member' => $memberId,
        'ip' => $ip,
    ];
}

ksort($groups, SORT_NATURAL);

if (empty($groups)) {
    echo '<div class="alert alert-info" role="alert">' . htmlspecialchars(__('No data available.'), ENT_QUOTES, 'UTF-8') . '</div>';

    return;
}

foreach ($groups as $groupId => $group) {
    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading"><h3 class="panel-title">' . htmlspecialchars(sprintf('Real Server Group %s', $groupId), ENT_QUOTES, 'UTF-8') . '</h3></div>';
    echo '<div class="panel-body"><div class="text-monospace">';
    echo htmlspecialchars(sprintf('Real Server Group %s', $groupId), ENT_QUOTES, 'UTF-8') . '<br>';

    foreach ($group['members'] as $member) {
        $line = '&gt; Real Server ' . htmlspecialchars($member['member'], ENT_QUOTES, 'UTF-8');
        if (! empty($member['ip'])) {
            $line .= ' (' . htmlspecialchars($member['ip'], ENT_QUOTES, 'UTF-8') . ')';
        }
        echo $line . '<br>';
    }

    echo '</div></div></div>';
}
