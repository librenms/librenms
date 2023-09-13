<?php
/**
 * applications.inc.php
 *
 * Discover applications
 *
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use App\Models\Application;
use App\Observers\ModuleModelObserver;
use LibreNMS\Config;

echo "\nApplications: ";

// fetch applications from the client
$results = snmpwalk_cache_oid($device, 'nsExtendStatus', [], 'NET-SNMP-EXTEND-MIB');

// Load our list of available applications
$applications = [];
if ($results) {
    foreach (glob(Config::get('install_dir') . '/includes/polling/applications/*.inc.php') as $file) {
        $name = basename($file, '.inc.php');
        $applications[$name] = $name;
    }

    // fix applications that don't match their snmp extend name
    $applications['dhcpstats'] = 'dhcp-stats';
    $applications['fbsdnfsclient'] = 'fbsd-nfs-client';
    $applications['fbsdnfsserver'] = 'fbsd-nfs-server';
    $applications['hv-monitor'] = 'hv-monitor';
    $applications['mailq'] = 'postfix';
    $applications['osupdate'] = 'os-updates';
    $applications['phpfpmsp'] = 'php-fpm';
    $applications['postfixdetailed'] = 'postfix';
    $applications['suricata-stats'] = 'suricata';
    $applications['sagan-stats'] = 'sagan';
}

d_echo(PHP_EOL . 'Available: ' . implode(', ', array_keys($applications)) . PHP_EOL);
d_echo('Checking for: ' . implode(', ', array_keys($results)) . PHP_EOL);

// Generate a list of enabled apps and a list of all discovered apps from the db
[$enabled_apps, $discovered_apps] = array_reduce(dbFetchRows(
    'SELECT `app_type`,`discovered` FROM `applications` WHERE `device_id`=? AND deleted_at IS NULL ORDER BY `app_type`',
    [$device['device_id']]
), function ($result, $app) {
    $result[0][] = $app['app_type'];
    if ($app['discovered']) {
        $result[1][] = $app['app_type'];
    }

    return $result;
}, [[], []]);

// enable observer for printing changes
ModuleModelObserver::observe(\App\Models\Application::class);

// Enable applications
$current_apps = [];
foreach ($results as $extend => $result) {
    if (isset($applications[$extend])) {
        $app = $applications[$extend];
        $current_apps[] = $app;

        if (! in_array($app, $enabled_apps)) {
            $app_obj = Application::withTrashed()->firstOrNew(['device_id' => $device['device_id'], 'app_type' => $app]);
            if ($app_obj->trashed()) {
                $app_obj->restore();
            }
            $app_obj->discovered = 1;
            $app_obj->save();
            log_event("Application enabled by discovery: $app", $device, 'application', 1);
        }
    }
}

// remove non-existing apps
$apps_to_remove = array_diff($discovered_apps, $current_apps);
DeviceCache::getPrimary()->applications()->whereIn('app_type', $apps_to_remove)->get()->each(function (Application $app) {
    $app->delete();
    \App\Models\Eventlog::log("Application disabled by discovery: $app->app_type", DeviceCache::getPrimary(), 'application', \LibreNMS\Enum\Severity::Notice);
});

// clean application_metrics
dbDeleteOrphans('application_metrics', ['applications.app_id']);

echo PHP_EOL;

unset(
    $applications,
    $enabled_apps,
    $discovered_apps,
    $current_apps,
    $apps_to_remove,
    $results,
    $file,
    $name,
    $extend,
    $app,
    $num
);
