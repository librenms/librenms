<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'mojo_cape_submit',
];

print_optionbar_start();

echo generate_link('General', $link_array);
echo ' | Slugs: ';

$slugs = $app->data['slugs'];

foreach (array_keys($slugs) as $index => $slug) {
    $slug = htmlspecialchars($slug);
    $label = $vars['slug'] == $slug
        ? '<span class="pagemenu-selected">' . $slug . '</span>'
        : $slug;

    echo generate_link($label, $link_array, ['slug' => $slug]);

    if ($index < (count($slugs) - 1)) {
        echo ', ';
    }
}

print_optionbar_end();

if (isset($vars['slug'])) {
    $vars['slug'] = htmlspecialchars($vars['slug']);
    $graphs = [
        'mojo_cape_submit_subs' => 'Submissions',
        'mojo_cape_submit_hash_changed' => 'Submissions where the hashes changed',
        'mojo_cape_submit_app_protos' => 'App protocols seen',
        'mojo_cape_submit_size_sum' => 'Size in bytes of submissions',
        'mojo_cape_submit_size_stats' => 'Size stats of submissions',
        'mojo_cape_submit_size_max' => 'Size max of any submissions',
        'mojo_cape_submit_size_mean' => 'Size mean of submissions',
        'mojo_cape_submit_size_mode' => 'Size mode of submissions',
        'mojo_cape_submit_size_median' => 'Size median of submissions',
        'mojo_cape_submit_size_min' => 'Size median of submissions',
        'mojo_cape_submit_size_stddev' => 'Size standard deviation of submissions',
    ];
} else {
    $graphs = [
        'mojo_cape_submit_subs' => 'Submissions',
        'mojo_cape_submit_subs_top12' => 'Submissions, top 12 slugs',
        'mojo_cape_submit_hash_changed' => 'Submissions where the hashes changed',
        'mojo_cape_submit_app_protos' => 'App protocols seen',
        'mojo_cape_submit_size_sum' => 'Size in bytes of submissions',
        'mojo_cape_submit_size_stats' => 'Size stats of submissions',
        'mojo_cape_submit_size_max' => 'Size max of any submissions',
        'mojo_cape_submit_size_mean' => 'Size mean of submissions',
        'mojo_cape_submit_size_mode' => 'Size mode of submissions',
        'mojo_cape_submit_size_median' => 'Size median of submissions',
        'mojo_cape_submit_size_min' => 'Size median of submissions',
        'mojo_cape_submit_size_stddev' => 'Size standard deviation of submissions',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['slug'])) {
        $graph_array['slug'] = $vars['slug'];
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
