<?php

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'cape';
try {
    $returned = json_app_get($device, $name, 1)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$current_packages = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'cape', 'pkg-dropped_files___-___');

// general RRD def for base stats
$rrd_name = ['app', $name, $app->app_id];
$rrd_def_general = RrdDefinition::make()
    ->addDataset('banned', 'GAUGE', 0)
    ->addDataset('completed', 'GAUGE', 0)
    ->addDataset('critical', 'GAUGE', 0)
    ->addDataset('debug', 'GAUGE', 0)
    ->addDataset('distributed', 'GAUGE', 0)
    ->addDataset('error', 'GAUGE', 0)
    ->addDataset('failed_analysis', 'GAUGE', 0)
    ->addDataset('failed_processing', 'GAUGE', 0)
    ->addDataset('failed_reporting', 'GAUGE', 0)
    ->addDataset('info', 'GAUGE', 0)
    ->addDataset('pending', 'GAUGE', 0)
    ->addDataset('recovered', 'GAUGE', 0)
    ->addDataset('reported', 'GAUGE', 0)
    ->addDataset('running', 'GAUGE', 0)
    ->addDataset('timedout', 'GAUGE', 0)
    ->addDataset('total_tasks', 'GAUGE', 0)
    ->addDataset('warning', 'GAUGE', 0)
    ->addDataset('wrong_prog', 'GAUGE', 0);
$fields = [
    'banned' => $returned['banned'] ?? null,
    'completed' => $returned['completed'] ?? null,
    'critical' => $returned['critical'] ?? null,
    'debug' => $returned['debug'] ?? null,
    'distributed' => $returned['distributed'] ?? null,
    'error' => $returned['error'] ?? null,
    'failed_analysis' => $returned['failed_analysis'] ?? null,
    'failed_processing' => $returned['failed_processing'] ?? null,
    'failed_reporting' => $returned['failed_reporting'] ?? null,
    'info' => $returned['info'] ?? null,
    'pending' => $returned['pending'] ?? null,
    'recovered' => $returned['recovered'] ?? null,
    'reported' => $returned['reported'] ?? null,
    'running' => $returned['running'] ?? null,
    'timedout' => $returned['timedout'] ?? null,
    'total_tasks' => $returned['total_tasks'] ?? null,
    'warning' => $returned['warning'] ?? null,
    'wrong_prog' => $returned['wrong_prog'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_general, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = $fields;

// aggregate dropped files stats
$rrd_name = ['app', $name, $app->app_id, 'dropped_files'];
$rrd_def_dropped_files = RrdDefinition::make()
    ->addDataset('dropped_files', 'GAUGE', 0)
    ->addDataset('s0dropped_files', 'GAUGE', 0)
    ->addDataset('s1dropped_files', 'GAUGE', 0)
    ->addDataset('s2dropped_files', 'GAUGE', 0)
    ->addDataset('s3dropped_files', 'GAUGE', 0)
    ->addDataset('s4dropped_files', 'GAUGE', 0)
    ->addDataset('s5dropped_files', 'GAUGE', 0)
    ->addDataset('s6dropped_files', 'GAUGE', 0)
    ->addDataset('s7dropped_files', 'GAUGE', 0)
    ->addDataset('s8dropped_files', 'GAUGE', 0)
    ->addDataset('s9dropped_files', 'GAUGE', 0);
$fields = [
    'dropped_files' => $returned['dropped_files'] ?? null,
    's0dropped_files' => $returned['min.dropped_files'] ?? null,
    's1dropped_files' => $returned['max.dropped_files'] ?? null,
    's2dropped_files' => $returned['range.dropped_files'] ?? null,
    's3dropped_files' => $returned['mean.dropped_files'] ?? null,
    's4dropped_files' => $returned['median.dropped_files'] ?? null,
    's5dropped_files' => $returned['mode.dropped_files'] ?? null,
    's6dropped_files' => $returned['v.dropped_files'] ?? null,
    's7dropped_files' => $returned['sd.dropped_files'] ?? null,
    's8dropped_files' => $returned['vp.dropped_files'] ?? null,
    's9dropped_files' => $returned['sdp.dropped_files'],
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_dropped_files, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate running procs stats
$rrd_name = ['app', $name, $app->app_id, 'running_processes'];
$rrd_def_running_processes = RrdDefinition::make()
    ->addDataset('running_processes', 'GAUGE', 0)
    ->addDataset('s0running_processes', 'GAUGE', 0)
    ->addDataset('s1running_processes', 'GAUGE', 0)
    ->addDataset('s2running_processes', 'GAUGE', 0)
    ->addDataset('s3running_processes', 'GAUGE', 0)
    ->addDataset('s4running_processes', 'GAUGE', 0)
    ->addDataset('s5running_processes', 'GAUGE', 0)
    ->addDataset('s6running_processes', 'GAUGE', 0)
    ->addDataset('s7running_processes', 'GAUGE', 0)
    ->addDataset('s8running_processes', 'GAUGE', 0)
    ->addDataset('s9running_processes', 'GAUGE', 0);
$fields_running_processes = [
    'running_processes' => $returned['running_processes'] ?? null,
    's0running_processes' => $returned['min.running_processes'] ?? null,
    's1running_processes' => $returned['max.running_processes'] ?? null,
    's2running_processes' => $returned['range.running_processes'] ?? null,
    's3running_processes' => $returned['mean.running_processes'] ?? null,
    's4running_processes' => $returned['median.running_processes'] ?? null,
    's5running_processes' => $returned['mode.running_processes'] ?? null,
    's6running_processes' => $returned['v.running_processes'] ?? null,
    's7running_processes' => $returned['sd.running_processes'] ?? null,
    's8running_processes' => $returned['vp.running_processes'] ?? null,
    's9running_processes' => $returned['sdp.running_processes'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_running_processes, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields_running_processes);
$metrics = array_merge($metrics, $fields_running_processes);

// aggregate api calls stats
$rrd_name = ['app', $name, $app->app_id, 'api_calls'];
$rrd_def_api_calls = RrdDefinition::make()
    ->addDataset('api_calls', 'GAUGE', 0)
    ->addDataset('s0api_calls', 'GAUGE', 0)
    ->addDataset('s1api_calls', 'GAUGE', 0)
    ->addDataset('s2api_calls', 'GAUGE', 0)
    ->addDataset('s3api_calls', 'GAUGE', 0)
    ->addDataset('s4api_calls', 'GAUGE', 0)
    ->addDataset('s5api_calls', 'GAUGE', 0)
    ->addDataset('s6api_calls', 'GAUGE', 0)
    ->addDataset('s7api_calls', 'GAUGE', 0)
    ->addDataset('s8api_calls', 'GAUGE', 0)
    ->addDataset('s9api_calls', 'GAUGE', 0);
$fields_api_calls = [
    'api_calls' => $returned['api_calls'] ?? null,
    's0api_calls' => $returned['min.api_calls'] ?? null,
    's1api_calls' => $returned['max.api_calls'] ?? null,
    's2api_calls' => $returned['range.api_calls'] ?? null,
    's3api_calls' => $returned['mean.api_calls'] ?? null,
    's4api_calls' => $returned['median.api_calls'] ?? null,
    's5api_calls' => $returned['mode.api_calls'] ?? null,
    's6api_calls' => $returned['v.api_calls'] ?? null,
    's7api_calls' => $returned['sd.api_calls'] ?? null,
    's8api_calls' => $returned['vp.api_calls'] ?? null,
    's9api_calls' => $returned['sdp.api_calls'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_api_calls, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields_api_calls);
$metrics = array_merge($metrics, $fields_api_calls);

// aggregate domains stats
$rrd_name = ['app', $name, $app->app_id, 'domains'];
$rrd_def_domains = RrdDefinition::make()
    ->addDataset('domains', 'GAUGE', 0)
    ->addDataset('s0domains', 'GAUGE', 0)
    ->addDataset('s1domains', 'GAUGE', 0)
    ->addDataset('s2domains', 'GAUGE', 0)
    ->addDataset('s3domains', 'GAUGE', 0)
    ->addDataset('s4domains', 'GAUGE', 0)
    ->addDataset('s5domains', 'GAUGE', 0)
    ->addDataset('s6domains', 'GAUGE', 0)
    ->addDataset('s7domains', 'GAUGE', 0)
    ->addDataset('s8domains', 'GAUGE', 0)
    ->addDataset('s9domains', 'GAUGE', 0);
$fields_domains = [
    'domains' => $returned['domains'] ?? null,
    's0domains' => $returned['min.domains'] ?? null,
    's1domains' => $returned['max.domains'] ?? null,
    's2domains' => $returned['range.domains'] ?? null,
    's3domains' => $returned['mean.domains'] ?? null,
    's4domains' => $returned['median.domains'] ?? null,
    's5domains' => $returned['mode.domains'] ?? null,
    's6domains' => $returned['v.domains'] ?? null,
    's7domains' => $returned['sd.domains'] ?? null,
    's8domains' => $returned['vp.domains'] ?? null,
    's9domains' => $returned['sdp.domains'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_domains, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields_domains);
$metrics = array_merge($metrics, $fields_domains);

// aggregate signatures total stats
$rrd_name = ['app', $name, $app->app_id, 'signatures_total'];
$rrd_def_signatures_total = RrdDefinition::make()
    ->addDataset('signatures_total', 'GAUGE', 0)
    ->addDataset('s0signatures_total', 'GAUGE', 0)
    ->addDataset('s1signatures_total', 'GAUGE', 0)
    ->addDataset('s2signatures_total', 'GAUGE', 0)
    ->addDataset('s3signatures_total', 'GAUGE', 0)
    ->addDataset('s4signatures_total', 'GAUGE', 0)
    ->addDataset('s5signatures_total', 'GAUGE', 0)
    ->addDataset('s6signatures_total', 'GAUGE', 0)
    ->addDataset('s7signatures_total', 'GAUGE', 0)
    ->addDataset('s8signatures_total', 'GAUGE', 0)
    ->addDataset('s9signatures_total', 'GAUGE', 0);
$fields_signatures_total = [
    'signatures_total' => $returned['signatures_total'] ?? null,
    's0signatures_total' => $returned['min.signatures_total'] ?? null,
    's1signatures_total' => $returned['max.signatures_total'] ?? null,
    's2signatures_total' => $returned['range.signatures_total'] ?? null,
    's3signatures_total' => $returned['mean.signatures_total'] ?? null,
    's4signatures_total' => $returned['median.signatures_total'] ?? null,
    's5signatures_total' => $returned['mode.signatures_total'] ?? null,
    's6signatures_total' => $returned['v.signatures_total'] ?? null,
    's7signatures_total' => $returned['sd.signatures_total'] ?? null,
    's8signatures_total' => $returned['vp.signatures_total'] ?? null,
    's9signatures_total' => $returned['sdp.signatures_total'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_signatures_total, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields_signatures_total);
$metrics = array_merge($metrics, $fields_signatures_total);

// aggregate signatures alert stats
$rrd_name = ['app', $name, $app->app_id, 'signatures_alert'];
$rrd_def_signatures_alert = RrdDefinition::make()
    ->addDataset('signatures_alert', 'GAUGE', 0)
    ->addDataset('s0signatures_alert', 'GAUGE', 0)
    ->addDataset('s1signatures_alert', 'GAUGE', 0)
    ->addDataset('s2signatures_alert', 'GAUGE', 0)
    ->addDataset('s3signatures_alert', 'GAUGE', 0)
    ->addDataset('s4signatures_alert', 'GAUGE', 0)
    ->addDataset('s5signatures_alert', 'GAUGE', 0)
    ->addDataset('s6signatures_alert', 'GAUGE', 0)
    ->addDataset('s7signatures_alert', 'GAUGE', 0)
    ->addDataset('s8signatures_alert', 'GAUGE', 0)
    ->addDataset('s9signatures_alert', 'GAUGE', 0);
$fields = [
    'signatures_alert' => $returned['signatures_alert'] ?? null,
    's0signatures_alert' => $returned['min.signatures_alert'] ?? null,
    's1signatures_alert' => $returned['max.signatures_alert'] ?? null,
    's2signatures_alert' => $returned['range.signatures_alert'] ?? null,
    's3signatures_alert' => $returned['mean.signatures_alert'] ?? null,
    's4signatures_alert' => $returned['median.signatures_alert'] ?? null,
    's5signatures_alert' => $returned['mode.signatures_alert'] ?? null,
    's6signatures_alert' => $returned['v.signatures_alert'] ?? null,
    's7signatures_alert' => $returned['sd.signatures_alert'] ?? null,
    's8signatures_alert' => $returned['vp.signatures_alert'] ?? null,
    's9signatures_alert' => $returned['sdp.signatures_alert'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_signatures_alert, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate reg keys mod stats
$rrd_name = ['app', $name, $app->app_id, 'reg_keys_mod'];
$rrd_def_reg_keys_mod = RrdDefinition::make()
    ->addDataset('reg_keys_mod', 'GAUGE', 0)
    ->addDataset('s0regkeysmod', 'GAUGE', 0)
    ->addDataset('s1regkeysmod', 'GAUGE', 0)
    ->addDataset('s2regkeysmod', 'GAUGE', 0)
    ->addDataset('s3regkeysmod', 'GAUGE', 0)
    ->addDataset('s4regkeysmod', 'GAUGE', 0)
    ->addDataset('s5regkeysmod', 'GAUGE', 0)
    ->addDataset('s6regkeysmod', 'GAUGE', 0)
    ->addDataset('s7regkeysmod', 'GAUGE', 0)
    ->addDataset('s8regkeysmod', 'GAUGE', 0)
    ->addDataset('s9regkeysmod', 'GAUGE', 0);
$fields = [
    'reg_keys_mod' => $returned['registry_keys_modified'] ?? null,
    's0regkeysmod' => $returned['min.registry_keys_modified'] ?? null,
    's1regkeysmod' => $returned['max.registry_keys_modified'] ?? null,
    's2regkeysmod' => $returned['range.registry_keys_modified'] ?? null,
    's3regkeysmod' => $returned['mean.registry_keys_modified'] ?? null,
    's4regkeysmod' => $returned['median.registry_keys_modified'] ?? null,
    's5regkeysmod' => $returned['mode.registry_keys_modified'] ?? null,
    's6regkeysmod' => $returned['v.registry_keys_modified'] ?? null,
    's7regkeysmod' => $returned['sd.registry_keys_modified'] ?? null,
    's8regkeysmod' => $returned['vp.registry_keys_modified'] ?? null,
    's9regkeysmod' => $returned['sdp.registry_keys_modified'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_reg_keys_mod, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate crash issues stats
$rrd_name = ['app', $name, $app->app_id, 'crash_issues'];
$rrd_def_crash_issues = RrdDefinition::make()
    ->addDataset('crash_issues', 'GAUGE', 0)
    ->addDataset('s0crash_issues', 'GAUGE', 0)
    ->addDataset('s1crash_issues', 'GAUGE', 0)
    ->addDataset('s2crash_issues', 'GAUGE', 0)
    ->addDataset('s3crash_issues', 'GAUGE', 0)
    ->addDataset('s4crash_issues', 'GAUGE', 0)
    ->addDataset('s5crash_issues', 'GAUGE', 0)
    ->addDataset('s6crash_issues', 'GAUGE', 0)
    ->addDataset('s7crash_issues', 'GAUGE', 0)
    ->addDataset('s8crash_issues', 'GAUGE', 0)
    ->addDataset('s9crash_issues', 'GAUGE', 0);
$fields = [
    'crash_issues' => $returned['crash_issues'] ?? null,
    's0crash_issues' => $returned['min.crash_issues'] ?? null,
    's1crash_issues' => $returned['max.crash_issues'] ?? null,
    's2crash_issues' => $returned['range.crash_issues'] ?? null,
    's3crash_issues' => $returned['mean.crash_issues'] ?? null,
    's4crash_issues' => $returned['median.crash_issues'] ?? null,
    's5crash_issues' => $returned['mode.crash_issues'] ?? null,
    's6crash_issues' => $returned['v.crash_issues'] ?? null,
    's7crash_issues' => $returned['sd.crash_issues'] ?? null,
    's8crash_issues' => $returned['vp.crash_issues'] ?? null,
    's9crash_issues' => $returned['sdp.crash_issues'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_crash_issues, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate anti issues stats
$rrd_name = ['app', $name, $app->app_id, 'anti_issues'];
$rrd_def_anti_issues = RrdDefinition::make()
    ->addDataset('anti_issues', 'GAUGE', 0)
    ->addDataset('s0anti_issues', 'GAUGE', 0)
    ->addDataset('s1anti_issues', 'GAUGE', 0)
    ->addDataset('s2anti_issues', 'GAUGE', 0)
    ->addDataset('s3anti_issues', 'GAUGE', 0)
    ->addDataset('s4anti_issues', 'GAUGE', 0)
    ->addDataset('s5anti_issues', 'GAUGE', 0)
    ->addDataset('s6anti_issues', 'GAUGE', 0)
    ->addDataset('s7anti_issues', 'GAUGE', 0)
    ->addDataset('s8anti_issues', 'GAUGE', 0)
    ->addDataset('s9anti_issues', 'GAUGE', 0);
$fields = [
    'anti_issues' => $returned['anti_issues'],
    's0anti_issues' => $returned['min.anti_issues'] ?? null,
    's1anti_issues' => $returned['max.anti_issues'] ?? null,
    's2anti_issues' => $returned['range.anti_issues'] ?? null,
    's3anti_issues' => $returned['mean.anti_issues'] ?? null,
    's4anti_issues' => $returned['median.anti_issues'] ?? null,
    's5anti_issues' => $returned['mode.anti_issues'] ?? null,
    's6anti_issues' => $returned['v.anti_issues'] ?? null,
    's7anti_issues' => $returned['sd.anti_issues'] ?? null,
    's8anti_issues' => $returned['vp.anti_issues'] ?? null,
    's9anti_issues' => $returned['sdp.anti_issues'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_anti_issues, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate files written stats
$rrd_name = ['app', $name, $app->app_id, 'files_written'];
$rrd_def_files_written = RrdDefinition::make()
   ->addDataset('files_written', 'GAUGE', 0)
    ->addDataset('s0files_written', 'GAUGE', 0)
    ->addDataset('s1files_written', 'GAUGE', 0)
    ->addDataset('s2files_written', 'GAUGE', 0)
    ->addDataset('s3files_written', 'GAUGE', 0)
    ->addDataset('s4files_written', 'GAUGE', 0)
    ->addDataset('s5files_written', 'GAUGE', 0)
    ->addDataset('s6files_written', 'GAUGE', 0)
    ->addDataset('s7files_written', 'GAUGE', 0)
    ->addDataset('s8files_written', 'GAUGE', 0)
    ->addDataset('s9files_written', 'GAUGE', 0);
$fields = [
    'files_written' => $returned['files_written'] ?? null,
    's0files_written' => $returned['min.files_written'] ?? null,
    's1files_written' => $returned['max.files_written'] ?? null,
    's2files_written' => $returned['range.files_written'] ?? null,
    's3files_written' => $returned['mean.files_written'] ?? null,
    's4files_written' => $returned['median.files_written'] ?? null,
    's5files_written' => $returned['mode.files_written'] ?? null,
    's6files_written' => $returned['v.files_written'] ?? null,
    's7files_written' => $returned['sd.files_written'] ?? null,
    's8files_written' => $returned['vp.files_written'] ?? null,
    's9files_written' => $returned['sdp.files_written'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_files_written, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate malscore stats
$rrd_name = ['app', $name, $app->app_id, 'malscore'];
$rrd_def_malscore = RrdDefinition::make()
    ->addDataset('malscore', 'GAUGE', 0)
    ->addDataset('s0malscore', 'GAUGE', 0)
    ->addDataset('s1malscore', 'GAUGE', 0)
    ->addDataset('s2malscore', 'GAUGE', 0)
    ->addDataset('s3malscore', 'GAUGE', 0)
    ->addDataset('s4malscore', 'GAUGE', 0)
    ->addDataset('s5malscore', 'GAUGE', 0)
    ->addDataset('s6malscore', 'GAUGE', 0)
    ->addDataset('s7malscore', 'GAUGE', 0)
    ->addDataset('s8malscore', 'GAUGE', 0)
    ->addDataset('s9malscore', 'GAUGE', 0);
$fields = [
    'malscore' => $returned['malscore'] ?? null,
    's0malscore' => $returned['min.malscore'] ?? null,
    's1malscore' => $returned['max.malscore'] ?? null,
    's2malscore' => $returned['range.malscore'] ?? null,
    's3malscore' => $returned['mean.malscore'] ?? null,
    's4malscore' => $returned['median.malscore'] ?? null,
    's5malscore' => $returned['mode.malscore'] ?? null,
    's6malscore' => $returned['v.malscore'] ?? null,
    's7malscore' => $returned['sd.malscore'] ?? null,
    's8malscore' => $returned['vp.malscore'] ?? null,
    's9malscore' => $returned['sdp.malscore'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_malscore, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate severity stats
$rrd_name = ['app', $name, $app->app_id, 'severity'];
$rrd_def_severity = RrdDefinition::make()
    ->addDataset('severity', 'GAUGE', 0)
    ->addDataset('s0severity', 'GAUGE', 0)
    ->addDataset('s1severity', 'GAUGE', 0)
    ->addDataset('s2severity', 'GAUGE', 0)
    ->addDataset('s3severity', 'GAUGE', 0)
    ->addDataset('s4severity', 'GAUGE', 0)
    ->addDataset('s5severity', 'GAUGE', 0)
    ->addDataset('s6severity', 'GAUGE', 0)
    ->addDataset('s7severity', 'GAUGE', 0)
    ->addDataset('s8severity', 'GAUGE', 0)
    ->addDataset('s9severity', 'GAUGE', 0);
$fields = [
    'severity' => $returned['severity'] ?? null,
    's0severity' => $returned['min.severity'] ?? null,
    's1severity' => $returned['max.severity'] ?? null,
    's2severity' => $returned['range.severity'] ?? null,
    's3severity' => $returned['mean.severity'] ?? null,
    's4severity' => $returned['median.severity'] ?? null,
    's5severity' => $returned['mode.severity'] ?? null,
    's6severity' => $returned['v.severity'] ?? null,
    's7severity' => $returned['sd.severity'] ?? null,
    's8severity' => $returned['vp.severity'] ?? null,
    's9severity' => $returned['sdp.severity'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_severity, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate confidence stats
$rrd_name = ['app', $name, $app->app_id, 'confidence'];
$rrd_def_confidence = RrdDefinition::make()
    ->addDataset('confidence', 'GAUGE', 0)
    ->addDataset('s0confidence', 'GAUGE', 0)
    ->addDataset('s1confidence', 'GAUGE', 0)
    ->addDataset('s2confidence', 'GAUGE', 0)
    ->addDataset('s3confidence', 'GAUGE', 0)
    ->addDataset('s4confidence', 'GAUGE', 0)
    ->addDataset('s5confidence', 'GAUGE', 0)
    ->addDataset('s6confidence', 'GAUGE', 0)
    ->addDataset('s7confidence', 'GAUGE', 0)
    ->addDataset('s8confidence', 'GAUGE', 0)
    ->addDataset('s9confidence', 'GAUGE', 0);
$fields = [
    'confidence' => $returned['confidence'] ?? null,
    's0confidence' => $returned['min.confidence'] ?? null,
    's1confidence' => $returned['max.confidence'] ?? null,
    's2confidence' => $returned['range.confidence'] ?? null,
    's3confidence' => $returned['mean.confidence'] ?? null,
    's4confidence' => $returned['median.confidence'] ?? null,
    's5confidence' => $returned['mode.confidence'] ?? null,
    's6confidence' => $returned['v.confidence'] ?? null,
    's7confidence' => $returned['sd.confidence'] ?? null,
    's8confidence' => $returned['vp.confidence'] ?? null,
    's9confidence' => $returned['sdp.confidence'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_confidence, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

// aggregate weight stats
$rrd_name = ['app', $name, $app->app_id, 'weight'];
$rrd_def_weight = RrdDefinition::make()
    ->addDataset('weight', 'GAUGE', 0)
    ->addDataset('s0weight', 'GAUGE', 0)
    ->addDataset('s1weight', 'GAUGE', 0)
    ->addDataset('s2weight', 'GAUGE', 0)
    ->addDataset('s3weight', 'GAUGE', 0)
    ->addDataset('s4weight', 'GAUGE', 0)
    ->addDataset('s5weight', 'GAUGE', 0)
    ->addDataset('s6weight', 'GAUGE', 0)
    ->addDataset('s7weight', 'GAUGE', 0)
    ->addDataset('s8weight', 'GAUGE', 0)
    ->addDataset('s9weight', 'GAUGE', 0);
$fields = [
    'weight' => $returned['weight'] ?? null,
    's0weight' => $returned['min.weight'] ?? null,
    's1weight' => $returned['max.weight'] ?? null,
    's2weight' => $returned['range.weight'] ?? null,
    's3weight' => $returned['mean.weight'] ?? null,
    's4weight' => $returned['median.weight'] ?? null,
    's5weight' => $returned['mode.weight'] ?? null,
    's6weight' => $returned['v.weight'] ?? null,
    's7weight' => $returned['sd.weight'] ?? null,
    's8weight' => $returned['vp.weight'] ?? null,
    's9weight' => $returned['sdp.weight'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_weight, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
$metrics = array_merge($metrics, $fields);

//
// process additional info returned
//

$rrd_def_pkg = RrdDefinition::make()
        ->addDataset('tasks', 'GAUGE', 0)
        ->addDataset('pending', 'GAUGE', 0)
        ->addDataset('banned', 'GAUGE', 0)
        ->addDataset('running', 'GAUGE', 0)
        ->addDataset('completed', 'GAUGE', 0)
        ->addDataset('distributed', 'GAUGE', 0)
        ->addDataset('reported', 'GAUGE', 0)
        ->addDataset('recovered', 'GAUGE', 0)
        ->addDataset('failed_analysis', 'GAUGE', 0)
        ->addDataset('failed_processing', 'GAUGE', 0);

$found_packages = [];
foreach ($returned['pkg_stats'] as $pkg => $stats) {
    $found_packages['pkg-dropped_files___-___-' . $pkg] = $pkg;

    $rrd_name = ['app', $name, $app->app_id, 'pkg___-___', $pkg];
    $fields = [
        'tasks' => $returned['pkg_stats'][$pkg]['tasks'] ?? null,
        'pending' => null,
        'banned' => $returned['pkg_stats'][$pkg]['banned'] ?? null,
        'running' => $returned['pkg_stats'][$pkg]['running'] ?? null,
        'completed' => $returned['pkg_stats'][$pkg]['completed'] ?? null,
        'distributed' => $returned['pkg_stats'][$pkg]['distributed'] ?? null,
        'reported' => $returned['pkg_stats'][$pkg]['reported'] ?? null,
        'recovered' => $returned['pkg_stats'][$pkg]['recovered'] ?? null,
        'failed_analysis' => $returned['pkg_stats'][$pkg]['failed_analysis'] ?? null,
        'failed_processing' => $returned['pkg_stats'][$pkg]['failed_processing'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_pkg, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-dropped_files___-___', $pkg];
    $fields = [
        'dropped_files' => $returned['pkg_stats'][$pkg]['dropped_files'] ?? null,
        's0dropped_files' => $returned['pkg_stats'][$pkg]['min.dropped_files'] ?? null,
        's1dropped_files' => $returned['pkg_stats'][$pkg]['max.dropped_files'] ?? null,
        's2dropped_files' => $returned['pkg_stats'][$pkg]['range.dropped_files'] ?? null,
        's3dropped_files' => $returned['pkg_stats'][$pkg]['mean.dropped_files'] ?? null,
        's4dropped_files' => $returned['pkg_stats'][$pkg]['median.dropped_files'] ?? null,
        's5dropped_files' => $returned['pkg_stats'][$pkg]['mode.dropped_files'] ?? null,
        's6dropped_files' => $returned['pkg_stats'][$pkg]['v.dropped_files'] ?? null,
        's7dropped_files' => $returned['pkg_stats'][$pkg]['sd.dropped_files'] ?? null,
        's8dropped_files' => $returned['pkg_stats'][$pkg]['vp.dropped_files'] ?? null,
        's9dropped_files' => $returned['pkg_stats'][$pkg]['sdp.dropped_files'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_dropped_files, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-running_processes___-___', $pkg];
    $fields = [
        'running_processes' => $returned['pkg_stats'][$pkg]['running_processes'] ?? null,
        's0running_processes' => $returned['pkg_stats'][$pkg]['min.running_processes'] ?? null,
        's1running_processes' => $returned['pkg_stats'][$pkg]['max.running_processes'] ?? null,
        's2running_processes' => $returned['pkg_stats'][$pkg]['range.running_processes'] ?? null,
        's3running_processes' => $returned['pkg_stats'][$pkg]['mean.running_processes'] ?? null,
        's4running_processes' => $returned['pkg_stats'][$pkg]['median.running_processes'] ?? null,
        's5running_processes' => $returned['pkg_stats'][$pkg]['mode.running_processes'] ?? null,
        's6running_processes' => $returned['pkg_stats'][$pkg]['v.running_processes'] ?? null,
        's7running_processes' => $returned['pkg_stats'][$pkg]['sd.running_processes'] ?? null,
        's8running_processes' => $returned['pkg_stats'][$pkg]['vp.running_processes'] ?? null,
        's9running_processes' => $returned['pkg_stats'][$pkg]['sdp.running_processes'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_running_processes, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-api_calls___-___', $pkg];
    $fields = [
        'api_calls' => $returned['pkg_stats'][$pkg]['api_calls'] ?? null,
        's0api_calls' => $returned['pkg_stats'][$pkg]['min.api_calls'] ?? null,
        's1api_calls' => $returned['pkg_stats'][$pkg]['max.api_calls'] ?? null,
        's2api_calls' => $returned['pkg_stats'][$pkg]['range.api_calls'] ?? null,
        's3api_calls' => $returned['pkg_stats'][$pkg]['mean.api_calls'] ?? null,
        's4api_calls' => $returned['pkg_stats'][$pkg]['median.api_calls'] ?? null,
        's5api_calls' => $returned['pkg_stats'][$pkg]['mode.api_calls'] ?? null,
        's6api_calls' => $returned['pkg_stats'][$pkg]['v.api_calls'] ?? null,
        's7api_calls' => $returned['pkg_stats'][$pkg]['sd.api_calls'] ?? null,
        's8api_calls' => $returned['pkg_stats'][$pkg]['vp.api_calls'] ?? null,
        's9api_calls' => $returned['pkg_stats'][$pkg]['sdp.api_calls'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_api_calls, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-domains___-___', $pkg];
    $fields = [
        'domains' => $returned['pkg_stats'][$pkg]['domains'] ?? null,
        's0domains' => $returned['pkg_stats'][$pkg]['min.domains'] ?? null,
        's1domains' => $returned['pkg_stats'][$pkg]['max.domains'] ?? null,
        's2domains' => $returned['pkg_stats'][$pkg]['range.domains'] ?? null,
        's3domains' => $returned['pkg_stats'][$pkg]['mean.domains'] ?? null,
        's4domains' => $returned['pkg_stats'][$pkg]['median.domains'] ?? null,
        's5domains' => $returned['pkg_stats'][$pkg]['mode.domains'] ?? null,
        's6domains' => $returned['pkg_stats'][$pkg]['v.domains'] ?? null,
        's7domains' => $returned['pkg_stats'][$pkg]['sd.domains'] ?? null,
        's8domains' => $returned['pkg_stats'][$pkg]['vp.domains'] ?? null,
        's9domains' => $returned['pkg_stats'][$pkg]['sdp.domains'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_domains, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-signatures_total___-___', $pkg];
    $fields = [
        'signatures_total' => $returned['pkg_stats'][$pkg]['signatures_total'] ?? null,
        's0signatures_total' => $returned['pkg_stats'][$pkg]['min.signatures_total'] ?? null,
        's1signatures_total' => $returned['pkg_stats'][$pkg]['max.signatures_total'] ?? null,
        's2signatures_total' => $returned['pkg_stats'][$pkg]['range.signatures_total'] ?? null,
        's3signatures_total' => $returned['pkg_stats'][$pkg]['mean.signatures_total'] ?? null,
        's4signatures_total' => $returned['pkg_stats'][$pkg]['median.signatures_total'] ?? null,
        's5signatures_total' => $returned['pkg_stats'][$pkg]['mode.signatures_total'] ?? null,
        's6signatures_total' => $returned['pkg_stats'][$pkg]['v.signatures_total'] ?? null,
        's7signatures_total' => $returned['pkg_stats'][$pkg]['sd.signatures_total'] ?? null,
        's8signatures_total' => $returned['pkg_stats'][$pkg]['vp.signatures_total'] ?? null,
        's9signatures_total' => $returned['pkg_stats'][$pkg]['sdp.signatures_total'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_signatures_total, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-signatures_alert___-___', $pkg];
    $fields = [
        'signatures_alert' => $returned['pkg_stats'][$pkg]['signatures_alert'] ?? null,
        's0signatures_alert' => $returned['pkg_stats'][$pkg]['min.signatures_alert'] ?? null,
        's1signatures_alert' => $returned['pkg_stats'][$pkg]['max.signatures_alert'] ?? null,
        's2signatures_alert' => $returned['pkg_stats'][$pkg]['range.signatures_alert'] ?? null,
        's3signatures_alert' => $returned['pkg_stats'][$pkg]['mean.signatures_alert'] ?? null,
        's4signatures_alert' => $returned['pkg_stats'][$pkg]['median.signatures_alert'] ?? null,
        's5signatures_alert' => $returned['pkg_stats'][$pkg]['mode.signatures_alert'] ?? null,
        's6signatures_alert' => $returned['pkg_stats'][$pkg]['v.signatures_alert'] ?? null,
        's7signatures_alert' => $returned['pkg_stats'][$pkg]['sd.signatures_alert'] ?? null,
        's8signatures_alert' => $returned['pkg_stats'][$pkg]['vp.signatures_alert'] ?? null,
        's9signatures_alert' => $returned['pkg_stats'][$pkg]['sdp.signatures_alert'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_signatures_alert, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-reg_keys_mod___-___', $pkg];
    $fields = [
        'reg_keys_mod' => $returned['pkg_stats'][$pkg]['registry_keys_modified'] ?? null,
        's0regkeysmod' => $returned['pkg_stats'][$pkg]['min.registry_keys_modified'] ?? null,
        's1regkeysmod' => $returned['pkg_stats'][$pkg]['max.registry_keys_modified'] ?? null,
        's2regkeysmod' => $returned['pkg_stats'][$pkg]['range.registry_keys_modified'] ?? null,
        's3regkeysmod' => $returned['pkg_stats'][$pkg]['mean.registry_keys_modified'] ?? null,
        's4regkeysmod' => $returned['pkg_stats'][$pkg]['median.registry_keys_modified'] ?? null,
        's5regkeysmod' => $returned['pkg_stats'][$pkg]['mode.registry_keys_modified'] ?? null,
        's6regkeysmod' => $returned['pkg_stats'][$pkg]['v.registry_keys_modified'] ?? null,
        's7regkeysmod' => $returned['pkg_stats'][$pkg]['sd.registry_keys_modified'] ?? null,
        's8regkeysmod' => $returned['pkg_stats'][$pkg]['vp.registry_keys_modified'] ?? null,
        's9regkeysmod' => $returned['pkg_stats'][$pkg]['sdp.registry_keys_modified'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_reg_keys_mod, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-crash_issues___-___', $pkg];
    $fields = [
        'crash_issues' => $returned['pkg_stats'][$pkg]['crash_issues'] ?? null,
        's0crash_issues' => $returned['pkg_stats'][$pkg]['min.crash_issues'] ?? null,
        's1crash_issues' => $returned['pkg_stats'][$pkg]['max.crash_issues'] ?? null,
        's2crash_issues' => $returned['pkg_stats'][$pkg]['range.crash_issues'] ?? null,
        's3crash_issues' => $returned['pkg_stats'][$pkg]['mean.crash_issues'] ?? null,
        's4crash_issues' => $returned['pkg_stats'][$pkg]['median.crash_issues'] ?? null,
        's5crash_issues' => $returned['pkg_stats'][$pkg]['mode.crash_issues'] ?? null,
        's6crash_issues' => $returned['pkg_stats'][$pkg]['v.crash_issues'] ?? null,
        's7crash_issues' => $returned['pkg_stats'][$pkg]['sd.crash_issues'] ?? null,
        's8crash_issues' => $returned['pkg_stats'][$pkg]['vp.crash_issues'] ?? null,
        's9crash_issues' => $returned['pkg_stats'][$pkg]['sdp.crash_issues'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_crash_issues, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-anti_issues___-___', $pkg];
    $fields = [
        'anti_issues' => $returned['pkg_stats'][$pkg]['anti_issues'] ?? null,
        's0anti_issues' => $returned['pkg_stats'][$pkg]['min.anti_issues'] ?? null,
        's1anti_issues' => $returned['pkg_stats'][$pkg]['max.anti_issues'] ?? null,
        's2anti_issues' => $returned['pkg_stats'][$pkg]['range.anti_issues'] ?? null,
        's3anti_issues' => $returned['pkg_stats'][$pkg]['mean.anti_issues'] ?? null,
        's4anti_issues' => $returned['pkg_stats'][$pkg]['median.anti_issues'] ?? null,
        's5anti_issues' => $returned['pkg_stats'][$pkg]['mode.anti_issues'] ?? null,
        's6anti_issues' => $returned['pkg_stats'][$pkg]['v.anti_issues'] ?? null,
        's7anti_issues' => $returned['pkg_stats'][$pkg]['sd.anti_issues'] ?? null,
        's8anti_issues' => $returned['pkg_stats'][$pkg]['vp.anti_issues'] ?? null,
        's9anti_issues' => $returned['pkg_stats'][$pkg]['sdp.anti_issues'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_anti_issues, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-files_written___-___', $pkg];
    $fields = [
        'files_written' => $returned['pkg_stats'][$pkg]['files_written'] ?? null,
        's0files_written' => $returned['pkg_stats'][$pkg]['min.files_written'] ?? null,
        's1files_written' => $returned['pkg_stats'][$pkg]['max.files_written'] ?? null,
        's2files_written' => $returned['pkg_stats'][$pkg]['range.files_written'] ?? null,
        's3files_written' => $returned['pkg_stats'][$pkg]['mean.files_written'] ?? null,
        's4files_written' => $returned['pkg_stats'][$pkg]['median.files_written'] ?? null,
        's5files_written' => $returned['pkg_stats'][$pkg]['mode.files_written'] ?? null,
        's6files_written' => $returned['pkg_stats'][$pkg]['v.files_written'] ?? null,
        's7files_written' => $returned['pkg_stats'][$pkg]['sd.files_written'] ?? null,
        's8files_written' => $returned['pkg_stats'][$pkg]['vp.files_written'] ?? null,
        's9files_written' => $returned['pkg_stats'][$pkg]['sdp.files_written'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_files_written, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-malscore___-___', $pkg];
    $fields = [
        'malscore' => $returned['pkg_stats'][$pkg]['malscore'] ?? null,
        's0malscore' => $returned['pkg_stats'][$pkg]['min.malscore'] ?? null,
        's1malscore' => $returned['pkg_stats'][$pkg]['max.malscore'] ?? null,
        's2malscore' => $returned['pkg_stats'][$pkg]['range.malscore'] ?? null,
        's3malscore' => $returned['pkg_stats'][$pkg]['mean.malscore'] ?? null,
        's4malscore' => $returned['pkg_stats'][$pkg]['median.malscore'] ?? null,
        's5malscore' => $returned['pkg_stats'][$pkg]['mode.malscore'] ?? null,
        's6malscore' => $returned['pkg_stats'][$pkg]['v.malscore'] ?? null,
        's7malscore' => $returned['pkg_stats'][$pkg]['sd.malscore'] ?? null,
        's8malscore' => $returned['pkg_stats'][$pkg]['vp.malscore'] ?? null,
        's9malscore' => $returned['pkg_stats'][$pkg]['sdp.malscore'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_malscore, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-confidence___-___', $pkg];
    $fields = [
        'confidence' => $returned['pkg_stats'][$pkg]['confidence'] ?? null,
        's0confidence' => $returned['pkg_stats'][$pkg]['min.confidence'] ?? null,
        's1confidence' => $returned['pkg_stats'][$pkg]['max.confidence'] ?? null,
        's2confidence' => $returned['pkg_stats'][$pkg]['range.confidence'] ?? null,
        's3confidence' => $returned['pkg_stats'][$pkg]['mean.confidence'] ?? null,
        's4confidence' => $returned['pkg_stats'][$pkg]['median.confidence'] ?? null,
        's5confidence' => $returned['pkg_stats'][$pkg]['mode.confidence'] ?? null,
        's6confidence' => $returned['pkg_stats'][$pkg]['v.confidence'] ?? null,
        's7confidence' => $returned['pkg_stats'][$pkg]['sd.confidence'] ?? null,
        's8confidence' => $returned['pkg_stats'][$pkg]['vp.confidence'] ?? null,
        's9confidence' => $returned['pkg_stats'][$pkg]['sdp.confidence'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_confidence, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);

    $rrd_name = ['app', $name, $app->app_id, 'pkg-weight___-___', $pkg];
    $fields = [
        'weight' => $returned['pkg_stats'][$pkg]['weight'],
        's0weight' => $returned['pkg_stats'][$pkg]['min.weight'] ?? null,
        's1weight' => $returned['pkg_stats'][$pkg]['max.weight'] ?? null,
        's2weight' => $returned['pkg_stats'][$pkg]['range.weight'] ?? null,
        's3weight' => $returned['pkg_stats'][$pkg]['mean.weight'] ?? null,
        's4weight' => $returned['pkg_stats'][$pkg]['median.weight'] ?? null,
        's5weight' => $returned['pkg_stats'][$pkg]['mode.weight'] ?? null,
        's6weight' => $returned['pkg_stats'][$pkg]['v.weight'] ?? null,
        's7weight' => $returned['pkg_stats'][$pkg]['sd.weight'] ?? null,
        's8weight' => $returned['pkg_stats'][$pkg]['vp.weight'] ?? null,
        's9weight' => $returned['pkg_stats'][$pkg]['sdp.weight'] ?? null,
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_weight, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);
}

// zero any rrds for existing packages
foreach ($current_packages as $index => $current_package) {
    $pkg = str_replace('pkg-dropped_files___-___-', '', $current_package);

    if (! isset($found_packages[$current_package])) {
        echo $pkg . " not handled, zeroing states for this timeslot\n";

        $rrd_name = ['app', $name, $app->app_id, 'pkg___-___', $pkg];
        $fields = [
            'tasks' => 0,
            'pending' => null,
            'banned' => 0,
            'running' => 0,
            'completed' => 0,
            'distributed' => 0,
            'reported' => 0,
            'recovered' => 0,
            'failed_analysis' => 0,
            'failed_processing' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_pkg, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-dropped_files___-___', $pkg];
        $fields = [
            'dropped_files' => 0,
            's0dropped_files' => 0,
            's1dropped_files' => 0,
            's2dropped_files' => 0,
            's3dropped_files' => 0,
            's4dropped_files' => 0,
            's5dropped_files' => 0,
            's6dropped_files' => 0,
            's7dropped_files' => 0,
            's8dropped_files' => 0,
            's9dropped_files' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_dropped_files, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-running_processes___-___', $pkg];
        $fields = [
            'running_processes' => 0,
            's0running_processes' => 0,
            's1running_processes' => 0,
            's2running_processes' => 0,
            's3running_processes' => 0,
            's4running_processes' => 0,
            's5running_processes' => 0,
            's6running_processes' => 0,
            's7running_processes' => 0,
            's8running_processes' => 0,
            's9running_processes' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_running_processes, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-api_calls___-___', $pkg];
        $fields = [
            'api_calls' => 0,
            's0api_calls' => 0,
            's1api_calls' => 0,
            's2api_calls' => 0,
            's3api_calls' => 0,
            's4api_calls' => 0,
            's5api_calls' => 0,
            's6api_calls' => 0,
            's7api_calls' => 0,
            's8api_calls' => 0,
            's9api_calls' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_api_calls, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-domains___-___', $pkg];
        $fields = [
            'domains' => 0,
            's0domains' => 0,
            's1domains' => 0,
            's2domains' => 0,
            's3domains' => 0,
            's4domains' => 0,
            's5domains' => 0,
            's6domains' => 0,
            's7domains' => 0,
            's8domains' => 0,
            's9domains' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_domains, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-signatures_total___-___', $pkg];
        $fields = [
            'signatures_total' => 0,
            's0signatures_total' => 0,
            's1signatures_total' => 0,
            's2signatures_total' => 0,
            's3signatures_total' => 0,
            's4signatures_total' => 0,
            's5signatures_total' => 0,
            's6signatures_total' => 0,
            's7signatures_total' => 0,
            's8signatures_total' => 0,
            's9signatures_total' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_signatures_total, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-signatures_alert___-___', $pkg];
        $fields = [
            'signatures_alert' => 0,
            's0signatures_alert' => 0,
            's1signatures_alert' => 0,
            's2signatures_alert' => 0,
            's3signatures_alert' => 0,
            's4signatures_alert' => 0,
            's5signatures_alert' => 0,
            's6signatures_alert' => 0,
            's7signatures_alert' => 0,
            's8signatures_alert' => 0,
            's9signatures_alert' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_signatures_alert, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-reg_keys_mod___-___', $pkg];
        $fields = [
            'reg_keys_mod' => 0,
            's0regkeysmod' => 0,
            's1regkeysmod' => 0,
            's2regkeysmod' => 0,
            's3regkeysmod' => 0,
            's4regkeysmod' => 0,
            's5regkeysmod' => 0,
            's6regkeysmod' => 0,
            's7regkeysmod' => 0,
            's8regkeysmod' => 0,
            's9regkeysmod' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_reg_keys_mod, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-crash_issues___-___', $pkg];
        $fields = [
            'crash_issues' => 0,
            's0crash_issues' => 0,
            's1crash_issues' => 0,
            's2crash_issues' => 0,
            's3crash_issues' => 0,
            's4crash_issues' => 0,
            's5crash_issues' => 0,
            's6crash_issues' => 0,
            's7crash_issues' => 0,
            's8crash_issues' => 0,
            's9crash_issues' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_crash_issues, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-anti_issues___-___', $pkg];
        $fields = [
            'anti_issues' => 0,
            's0anti_issues' => 0,
            's1anti_issues' => 0,
            's2anti_issues' => 0,
            's3anti_issues' => 0,
            's4anti_issues' => 0,
            's5anti_issues' => 0,
            's6anti_issues' => 0,
            's7anti_issues' => 0,
            's8anti_issues' => 0,
            's9anti_issues' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_anti_issues, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-files_written___-___', $pkg];
        $fields = [
            'files_written' => 0,
            's0files_written' => 0,
            's1files_written' => 0,
            's2files_written' => 0,
            's3files_written' => 0,
            's4files_written' => 0,
            's5files_written' => 0,
            's6files_written' => 0,
            's7files_written' => 0,
            's8files_written' => 0,
            's9files_written' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_files_written, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-malscore___-___', $pkg];
        $fields = [
            'malscore' => 0,
            's0malscore' => 0,
            's1malscore' => 0,
            's2malscore' => 0,
            's3malscore' => 0,
            's4malscore' => 0,
            's5malscore' => 0,
            's6malscore' => 0,
            's7malscore' => 0,
            's8malscore' => 0,
            's9malscore' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_malscore, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-confidence___-___', $pkg];
        $fields = [
            'confidence' => 0,
            's0confidence' => 0,
            's1confidence' => 0,
            's2confidence' => 0,
            's3confidence' => 0,
            's4confidence' => 0,
            's5confidence' => 0,
            's6confidence' => 0,
            's7confidence' => 0,
            's8confidence' => 0,
            's9confidence' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_confidence, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);

        $rrd_name = ['app', $name, $app->app_id, 'pkg-weight___-___', $pkg];
        $fields = [
            'weight' => 0,
            's0weight' => 0,
            's1weight' => 0,
            's2weight' => 0,
            's3weight' => 0,
            's4weight' => 0,
            's5weight' => 0,
            's6weight' => 0,
            's7weight' => 0,
            's8weight' => 0,
            's9weight' => 0,
        ];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_weight, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $fields);
    } else {
        echo $pkg . " handled, skipping zeroing this timeslot\n";
    }
}

// log any warnings
if (count($returned['warnings']) > 0) {
    $log_message = 'CAPE Warns: ' . json_encode($returned['warnings']);
    Eventlog::log($log_message, $device['device_id'], 'application', Severity::Warning);
}

// log any criticals
if (count($returned['criticals']) > 0) {
    $log_message = 'CAPE Criticals: ' . json_encode($returned['criticals']);
    Eventlog::log($log_message, $device['device_id'], 'application', Severity::Error);
}

// log any criticals
if (count($returned['errors']) > 0) {
    $log_message = 'CAPE Errors: ' . json_encode($returned['errors']);
    Eventlog::log($log_message, $device['device_id'], 'application', Severity::Error);
}

update_application($app, 'OK', $metrics);
