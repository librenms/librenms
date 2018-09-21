
<a href="' .generate_device_url($device). '" title="' . $device_system_name . " - " . formatUptime($device['uptime']) . '">
<div class="device-availability ' . $deviceState . '" style="width:' . $config['webui']['availability_map_box_size'] . 'px;">
    <span class="availability-label label ' . $deviceLabel . ' label-font-border">' . $deviceState . '</span>
    <span class="device-icon">' . $deviceIcon . '</span><br>
    <span class="small">' . shorthost($device_system_name) . '</span>
</div>
</a>';
} else {
if ($widget_settings['color_only_select'] == 1) {
$deviceState = ' ';
$deviceLabel .= ' widget-availability-fixed';
}
$temp_output[] = '
<a href="' .generate_device_url($device). '" title="' . $device_system_name . " - " . formatUptime($device['uptime']) . '">
<span class="label ' . $deviceLabel . ' widget-availability label-font-border">' . $deviceState . '</span>
</a>
