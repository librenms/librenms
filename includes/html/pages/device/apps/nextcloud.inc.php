<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'nextcloud',
];

$multimount = $app->data['multimount'] ?? 0;
$user_last_seen = $app->data['user_last_seen'] ?? [];

if (isset($vars['nextcloud_user'])) {
    $vars['nextcloud_user'] = htmlspecialchars($vars['nextcloud_user']);
}

print_optionbar_start();

$label = ! isset($vars['nextcloud_user'])
    ? '<span class="pagemenu-selected">Basics</span>'
    : 'Basics';
echo generate_link($label, $link_array);
echo ' | Users: ';
$nextcloud_users = $app->data['users'] ?? [];
sort($nextcloud_users);
foreach ($nextcloud_users as $index => $nextcloud_user) {
    $nextcloud_user = htmlspecialchars($nextcloud_user);
    $label = $vars['nextcloud_user'] == $nextcloud_user
        ? '<span class="pagemenu-selected">' . $nextcloud_user . '</span>'
        : $nextcloud_user;

    echo generate_link($label, $link_array, ['nextcloud_user' => $nextcloud_user]) . "\n";

    if ($index < (count($nextcloud_users) - 1)) {
        echo ', ';
    }
}

if (isset($vars['nextcloud_user']) && isset($user_last_seen[$vars['nextcloud_user']])) {
    if ($user_last_seen[$vars['nextcloud_user']] == '1970-01-01T00:00:00+00:00') {
        echo '<br>Last Seen: never';
    } else {
        echo '<br>Last Seen: ' . htmlspecialchars($user_last_seen[$vars['nextcloud_user']]);
    }
}

print_optionbar_end();

$graphs = [];
if (isset($vars['nextcloud_user'])) {
    $graphs['nextcloud_used'] = 'Used Storage Space, bytes';
    $graphs['nextcloud_user_storage'] = 'User Storage Space, bytes';
    $graphs['nextcloud_last_seen'] = 'Last Seen Ago, seconds';
    $graphs['nextcloud_calendars'] = 'Calendars';
    $graphs['nextcloud_quota'] = 'Storage Quota, bytes';
    $graphs['nextcloud_relative'] = 'Storage Relative, pecent';
} else {
    $graphs['nextcloud_used'] = 'Used Storage Space, bytes';
    if (! $multimount) {
        $graphs['nextcloud_storage'] = 'Storage Space, bytes';
        $graphs['nextcloud_storage_with_quota'] = 'Storage Space And Quota, bytes';
    }
    $graphs['nextcloud_calendars'] = 'Calendars';
    $graphs['nextcloud_disabled_apps'] = 'Disabled Apps';
    $graphs['nextcloud_enabled_apps'] = 'Enabled Apps';
    $graphs['nextcloud_encryption_enabled'] = 'Encryption Enabled';
    $graphs['nextcloud_user_count'] = 'User Count';
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['nextcloud_user'])) {
        $graph_array['nextcloud_user'] = $vars['nextcloud_user'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
