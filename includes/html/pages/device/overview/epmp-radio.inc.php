<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\Sensor;
use LibreNMS\Util\Html;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

if (! function_exists('librenms_epmp_radio_tx_power_sensor')) {
    function librenms_epmp_radio_tx_power_sensor()
    {
        return DeviceCache::getPrimary()->sensors
            ->where('sensor_class', Sensor::Dbm->value)
            ->first(fn ($sensor) => $sensor->group === 'Radio' && $sensor->sensor_descr === 'TX Power');
    }
}

if (! function_exists('librenms_epmp_radio_state_sensors')) {
    function librenms_epmp_radio_state_sensors()
    {
        return DeviceCache::getPrimary()->sensors
            ->where('sensor_class', Sensor::State->value)
            ->where('group', 'Radio')
            ->sortBy('sensor_descr')
            ->values();
    }
}

if (! function_exists('librenms_epmp_radio_antenna_gain')) {
    function librenms_epmp_radio_antenna_gain()
    {
        $value = DeviceCache::getPrimary()->getAttrib('epmp_radio_antenna_gain_dbi');

        return is_numeric($value) ? $value : null;
    }
}

if (! function_exists('librenms_epmp_has_radio_overview_rows')) {
    function librenms_epmp_has_radio_overview_rows(): bool
    {
        return librenms_epmp_radio_tx_power_sensor() !== null
            || librenms_epmp_radio_state_sensors()->isNotEmpty()
            || librenms_epmp_radio_antenna_gain() !== null;
    }
}

if (! function_exists('librenms_epmp_render_sensor_overview_row')) {
    function librenms_epmp_render_sensor_overview_row(array $device, $sensor, Sensor $sensor_class): void
    {
        $graph_array = [
            'height' => '100',
            'width' => '210',
            'to' => LibrenmsConfig::get('time.now'),
            'id' => $sensor->sensor_id,
            'type' => 'sensor_' . $sensor_class->value,
            'from' => LibrenmsConfig::get('time.day'),
            'legend' => 'no',
        ];

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = Url::generate($link_array);

        if ($sensor->poller_type == 'ipmi') {
            $sensor_descr = substr((string) ipmiSensorName($device['hardware'], $sensor->sensor_descr), 0, 48);
        } else {
            $sensor_descr = substr((string) $sensor->sensor_descr, 0, 48);
        }

        $overlib_content = '<div class=overlib><span class=overlib-text>' . $device['hostname'] . ' - ' . $sensor_descr . '</span><br />';
        foreach (['day', 'week', 'month', 'year'] as $period) {
            $graph_array['from'] = LibrenmsConfig::get("time.$period");
            $overlib_content .= str_replace('"', "\'", Url::graphTag($graph_array));
        }
        $overlib_content .= '</div>';

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        $graph_array['from'] = LibrenmsConfig::get('time.day');
        $sensor_minigraph = Url::lazyGraphTag($graph_array);
        $sensor_current = Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());

        echo '<tr><td><div style="display: grid; grid-gap: 10px; grid-template-columns: 3fr 1fr 1fr;">
            <div>' . Url::overlibLink($link, Rewrite::shortenIfName($sensor_descr), $overlib_content, $sensor_class->value) . '</div>
            <div>' . Url::overlibLink($link, $sensor_minigraph, $overlib_content, $sensor_class->value) . '</div>
            <div>' . Url::overlibLink($link, $sensor_current, $overlib_content, $sensor_class->value) . '</div>
            </div></td></tr>';
    }
}

if (! function_exists('librenms_epmp_render_metadata_overview_row')) {
    function librenms_epmp_render_metadata_overview_row(string $label, string $value): void
    {
        echo '<tr><td><div style="display: grid; grid-gap: 10px; grid-template-columns: 3fr 1fr 1fr;">
            <div>' . htmlspecialchars($label, ENT_QUOTES) . '</div>
            <div></div>
            <div>' . htmlspecialchars($value, ENT_QUOTES) . '</div>
            </div></td></tr>';
    }
}

if (! function_exists('librenms_epmp_render_radio_overview_rows')) {
    function librenms_epmp_render_radio_overview_rows(array $device): void
    {
        if (! librenms_epmp_has_radio_overview_rows()) {
            return;
        }

        echo "<tr><td colspan='3'><strong>Radio</strong></td></tr>";

        $tx_power = librenms_epmp_radio_tx_power_sensor();
        if ($tx_power) {
            librenms_epmp_render_sensor_overview_row($device, $tx_power, Sensor::Dbm);
        }

        $antenna_gain = librenms_epmp_radio_antenna_gain();
        if ($antenna_gain !== null) {
            librenms_epmp_render_metadata_overview_row('Antenna Gain', $antenna_gain . ' dBi');
        }

        foreach (librenms_epmp_radio_state_sensors() as $sensor) {
            librenms_epmp_render_sensor_overview_row($device, $sensor, Sensor::State);
        }
    }
}
