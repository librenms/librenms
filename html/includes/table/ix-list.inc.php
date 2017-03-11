<?php

$asn = clean($_POST['asn']);

$file_cache = false;
$json_file = $config['install_dir'] . '/cache/ix-list-' . $asn . '.json';
if (is_file($json_file)) {
    $file_time = filemtime($json_file);
    if (time() - $file_time > 86400) {
        unlink($json_file);
    } else {
        $json_data = file_get_contents($json_file);
        $file_cache = true;
    }
}

if ($file_cache === false) {
    $get = Requests::get('https://peeringdb.com/api/net?depth=2&asn=' . $asn);
    $json_data = $get->body;
    file_put_contents($json_file, $get->body);
}

$data = json_decode($json_data);
$ixs = $data->{'data'}{0}->{'netixlan_set'};

$total = count($ixs);
foreach ($ixs as $ix) {
    $response[] = array(
        'exchange' => $ix->{'name'},
        'action'   => "<a class='btn btn-sm btn-primary' href='" . generate_url(array('page' => 'peering', 'section' => 'ix-peers', 'asn' => $asn['bgpLocalAs'], 'ixid' => $ix->{'ix_id'})) . "' role='button'>Show Peers</a>"
    );
}



$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
