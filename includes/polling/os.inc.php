<?php

if (is_file($config['install_dir'].'/includes/polling/os/'.$device['os'].'.inc.php')) {
    // OS Specific
    include $config['install_dir'].'/includes/polling/os/'.$device['os'].'.inc.php';
} elseif ($device['os_group'] && is_file($config['install_dir'].'/includes/polling/os/'.$device['os_group'].'.inc.php')) {
    // OS Group Specific
    include $config['install_dir'].'/includes/polling/os/'.$device['os_group'].'.inc.php';
} else {
    echo "Generic :(\n";
}

if ($version && $device['version'] != $version) {
    $update_array['version'] = $version;
    log_event('OS Version -> '.$version, $device, 'system');
}

if ($features != $device['features']) {
    $update_array['features'] = $features;
    log_event('OS Features -> '.$features, $device, 'system');
}

if ($hardware && $hardware != $device['hardware']) {
    $update_array['hardware'] = $hardware;
    log_event('Hardware -> '.$hardware, $device, 'system');
}

if ($serial && $serial != $device['serial']) {
    $update_array['serial'] = $serial;
    log_event('Serial -> '.$serial, $device, 'system');
}

if ($icon && $icon != $device['icon']) {
    $update_array['icon'] = $icon;
    log_event('Icon -> '.$icon, $device, 'system');
}

echo "\nHardware: ".$hardware.' Version: '.$version.' Features: '.$features.' Serial: '.$serial."\n";
