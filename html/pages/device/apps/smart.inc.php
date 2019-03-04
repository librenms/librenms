<?php
/*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @subpackage webui
* @link       http://librenms.org
* @copyright  2019 LibreNMS
* @author     LibreNMS Contributors
*/

global $config;

$disks = get_disks_with_smart($device, $app['app_id']);

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'smart',
];

$drives = [];

foreach ($disks as $label) {
    $disk = $label;

    if ($vars['disk'] == $disk) {
        $label = sprintf('⚫ %s', $label);
    }

    array_push($drives, generate_link($label, $link_array, ['disk' => $disk]));
}

printf("%s | drives: %s", generate_link('All Drives', $link_array), implode(', ', $drives));

print_optionbar_end();

if (isset($vars['disk'])) {
    $graphs = [
        'smart_big5' => 'Reliability / Age',
        'smart_temp' => 'Temperature',
        'smart_ssd' => 'SSD-specific',
        'smart_other' => 'Other',
        'smart_tests_status' => 'S.M.A.R.T self-tests results',
        'smart_tests_ran' => 'S.M.A.R.T self-tests run count',
    ];
} else {
    $graphs = [
        'smart_id5' => 'ID# 5, Reallocated Sectors Count',
        'smart_id10' => 'ID# 10, Spin Retry Count',
        'smart_id173' => 'ID# 173, SSD Wear Leveller Worst Case Erase Count',
        'smart_id177' => 'ID# 177, SSD Wear Leveling Count',
        'smart_id183' => 'ID# 183, Detected Uncorrectable Bad Blocks',
        'smart_id184' => 'ID# 184, End-to-End error / IOEDC',
        'smart_id187' => 'ID# 187, Reported Uncorrectable Errors',
        'smart_id188' => 'ID# 188, Command Timeout',
        'smart_id190' => 'ID# 190, Airflow Temperature (C)',
        'smart_id194' => 'ID# 194, Temperature (C)',
        'smart_id196' => 'ID# 196, Reallocation Event Count',
        'smart_id197' => 'ID# 197, Current Pending Sector Count',
        'smart_id198' => 'ID# 198, Uncorrectable Sector Count / Offline Uncorrectable / Off-Line Scan Uncorrectable Sector Count',
        'smart_id199' => 'ID# 199, UltraDMA CRC Error Count',
        'smart_id231' => 'ID# 231, SSD Life Left',
        'smart_id233' => 'ID# 233, Media Wearout Indicator'
    ];
}

include 'app.bootstrap.inc.php';
