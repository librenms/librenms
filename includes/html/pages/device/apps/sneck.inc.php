 <?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'sneck',
];

if (isset($vars['clear'])) {
    del_sneck_data($device['device_id']);
} else {
    $sneck_data = get_sneck_data($device['device_id']);

    if (isset($sneck_data) ) {
        print_optionbar_start();
        echo "There is saved data, likely from some sort of error. Please bottom of page to review and clear.";
        print_optionbar_end();
    }
}

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'sneck',
];

$graphs = [
    'sneck_results'=>'Results',
    'sneck_time'=>'Time Difference',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

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

# print it if we got data
if (isset($sneck_data) ) {
    print_optionbar_start();

    echo "<pre>" . json_encode($sneck_data, JSON_PRETTY_PRINT) . "</pre>";

    echo generate_link('Click here to clear.', $link_array, ['clear'=>1]) . $append;

    print_optionbar_end();
}
