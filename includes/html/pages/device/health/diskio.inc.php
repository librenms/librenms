<?php

$diskioViews = [
    'physical' => 'Physical Drives',
    'logical' => 'Logical Drives',
    'all' => 'All Drives',
];

$selection = \LibreNMS\Util\DiskIoFilter::normalizeSelection($vars['diskio_view'] ?? null, $vars['diskio_subtype'] ?? null);
$selectedDiskioView = $selection['view'];
$selectedDiskioSubtype = $selection['subtype'];

$diskioSubtypes = [
    'physical' => [
        'all' => 'All Physical',
        'sd_family' => 'SATA/SCSI/Virtual',
        'nvme' => 'NVMe Drives',
        'mmcblk' => 'MMC/SD Drives',
        'other' => 'Other Physical',
    ],
    'logical' => [
        'all' => 'All Logical',
        'partitions' => 'Partitions',
        'dm' => 'Device Mapper',
        'md' => 'Software RAID',
        'loop' => 'Loop Devices',
        'other' => 'Other Logical',
    ],
];

$diskioLinkArray = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'health',
    'metric' => 'diskio',
];

print_optionbar_start();
echo "<span style='font-weight: bold;'>Drives</span> &#187; ";
$sep = '';
foreach ($diskioViews as $diskioView => $text) {
    echo $sep;
    if ($selectedDiskioView == $diskioView) {
        echo '<span class="pagemenu-selected">';
    }

    echo generate_link($text, $diskioLinkArray, ['diskio_view' => $diskioView]);
    if ($selectedDiskioView == $diskioView) {
        echo '</span>';
    }

    $sep = ' | ';
}

if (in_array($selectedDiskioView, ['physical', 'logical'], true)) {
    echo '<br><span style="padding-left: 22px;"><strong>Type</strong> &#187; ';
    $sep = '';
    foreach ($diskioSubtypes[$selectedDiskioView] as $diskioSubtype => $text) {
        echo $sep;
        if ($selectedDiskioSubtype == $diskioSubtype) {
            echo '<span class="pagemenu-selected">';
        }

        echo generate_link($text, $diskioLinkArray, ['diskio_view' => $selectedDiskioView, 'diskio_subtype' => $diskioSubtype]);
        if ($selectedDiskioSubtype == $diskioSubtype) {
            echo '</span>';
        }

        $sep = ' | ';
    }
    echo '</span>';
}

print_optionbar_end();

$viewDescriptions = [
    'physical' => 'Physical drives are whole block devices (for example sda, nvme0n1, mmcblk0).',
    'logical' => 'Logical drives are partitions and virtual devices (for example sda1, nvme0n1p1, dm-0, md0, loop0).',
    'all' => 'All drives shows both physical and logical disk I/O entries.',
];

$subtypeDescriptions = [
    'physical' => [
        'all' => 'All physical device families.',
        'sd_family' => 'Classic Linux disk families: sd*, hd*, vd*, and xvd*.',
        'nvme' => 'NVMe namespaces such as nvme0n1.',
        'mmcblk' => 'MMC and SD block devices such as mmcblk0.',
        'other' => 'Physical drives that do not match a known family.',
    ],
    'logical' => [
        'all' => 'All logical device types.',
        'partitions' => 'Disk partitions such as sda1, nvme0n1p1, and mmcblk0p1.',
        'dm' => 'Device mapper volumes named dm-*.',
        'md' => 'Linux software RAID devices named md*.',
        'loop' => 'Loopback devices named loop*.',
        'other' => 'Logical drives that do not match a known type.',
    ],
];

echo '<div style="padding: 6px 0 10px 0; color: #777;">';
echo $viewDescriptions[$selectedDiskioView];
if (isset($subtypeDescriptions[$selectedDiskioView][$selectedDiskioSubtype])) {
    echo ' ' . $subtypeDescriptions[$selectedDiskioView][$selectedDiskioSubtype];
}
echo '</div>';

$row = 1;

foreach (dbFetchRows('SELECT * FROM `ucd_diskio` WHERE device_id = ? ORDER BY diskio_descr', [$device['device_id']]) as $drive) {
    $driveType = \LibreNMS\Util\DiskIoFilter::classify($drive['diskio_descr']);
    if (! \LibreNMS\Util\DiskIoFilter::matches($driveType, $selectedDiskioView, $selectedDiskioSubtype)) {
        continue;
    }

    if (is_int($row / 2)) {
        $row_colour = \App\Facades\LibrenmsConfig::get('list_colour.even');
    } else {
        $row_colour = \App\Facades\LibrenmsConfig::get('list_colour.odd');
    }

    $fs_url = 'device/device=' . $device['device_id'] . '/tab=health/metric=diskio/';
    if ($selectedDiskioView !== 'all') {
        $fs_url .= 'diskio_view=' . $selectedDiskioView . '/';
        if ($selectedDiskioSubtype !== 'all') {
            $fs_url .= 'diskio_subtype=' . $selectedDiskioSubtype . '/';
        }
    }

    $graph_array_zoom['id'] = $drive['diskio_id'];
    $graph_array_zoom['type'] = 'diskio_ops';
    $graph_array_zoom['width'] = '400';
    $graph_array_zoom['height'] = '125';
    $graph_array_zoom['from'] = \App\Facades\LibrenmsConfig::get('time.twoday');
    $graph_array_zoom['to'] = \App\Facades\LibrenmsConfig::get('time.now');

    $overlib_link = \LibreNMS\Util\Url::overlibLink($fs_url, $drive['diskio_descr'], \LibreNMS\Util\Url::graphTag($graph_array_zoom));

    $types = [
        'diskio_bits',
        'diskio_ops',
    ];

    foreach ($types as $graph_type) {
        $graph_array = [];
        $graph_array['id'] = $drive['diskio_id'];
        $graph_array['type'] = $graph_type;
        if ($graph_array['type'] == 'diskio_ops') {
            $graph_type_title = 'Ops/sec';
        }
        if ($graph_array['type'] == 'diskio_bits') {
            $graph_type_title = 'bps';
        }
        echo "<div class='panel panel-default'>
                <div class='panel-heading'>
                <h3 class='panel-title'>$overlib_link - $graph_type_title</h3>
            </div>";
        echo "<div class='panel-body'>";
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div>';
    }

    $row++;
}
