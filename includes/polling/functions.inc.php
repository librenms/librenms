<?php

use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppBase64DecodeException;
use LibreNMS\Exceptions\JsonAppBlankJsonException;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\Exceptions\JsonAppGzipDecodeException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\Exceptions\JsonAppPollingFailedException;
use LibreNMS\Exceptions\JsonAppWrongVersionException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Oid;
use LibreNMS\Util\ProgressList;

/**
 * Update the application status and output in the database.
 *
 * Metric values should have key for of the matching name.
 * If you have multiple groups of metrics, you can group them with multiple sub arrays
 * The group name (key) will be prepended to each metric in that group, separated by an underscore
 * The special group "none" will not be prefixed.
 *
 * @param  \App\Models\Application  $app  app from the db, including app_id
 * @param  string  $response  This should be the return state of Application polling
 * @param  array  $metrics  an array of additional metrics to store in the database for alerting
 * @param  string  $status  This is the current value for alerting
 */
function update_application($app, $response, $metrics = [], $status = '')
{
    if (! $app) {
        d_echo('$app does not exist, could not update');

        return;
    }

    $app->app_state = 'UNKNOWN';
    $app->app_status = $status;
    $app->timestamp = DB::raw('NOW()');

    if ($response != '' && $response !== false) {
        // if the response indicates an error, set it and set app_status to the raw response
        if (Str::contains($response, [
            'Traceback (most recent call last):',
            'Connection refused',
        ])) {
            $app->app_state = 'ERROR';
            $app->app_status = $response;
        } elseif (preg_match('/^(ERROR|LEGACY|UNSUPPORTED)/', $response, $matches)) {
            $app->app_state = $matches[1];
            $app->app_status = $response;
        } else {
            // should maybe be 'unknown' as state
            $app->app_state = 'OK';
        }
    }

    if ($app->isDirty('app_state')) {
        $app->app_state_prev = $app->getOriginal('app_state');

        switch ($app->app_state) {
            case 'OK':
                $severity = Severity::Ok;
                $event_msg = 'changed to OK';
                break;
            case 'ERROR':
                $severity = Severity::Error;
                $event_msg = 'ends with ERROR';
                break;
            case 'LEGACY':
                $severity = Severity::Warning;
                $event_msg = 'Client Agent is deprecated';
                break;
            case 'UNSUPPORTED':
                $severity = Severity::Error;
                $event_msg = 'Client Agent Version is not supported';
                break;
            default:
                $severity = Severity::Unknown;
                $event_msg = 'has UNKNOWN state';
                break;
        }
        \App\Models\Eventlog::log('Application ' . $app->displayName() . ' ' . $event_msg, DeviceCache::getPrimary(), 'application', $severity);
    }

    $app->save();

    // update metrics
    if (! empty($metrics)) {
        $db_metrics = dbFetchRows('SELECT * FROM `application_metrics` WHERE app_id=?', [$app['app_id']]);
        $db_metrics = array_by_column($db_metrics, 'metric');

        // allow two level metrics arrays, flatten them and prepend the group name
        if (is_array(current($metrics))) {
            $metrics = array_reduce(
                array_keys($metrics),
                function ($carry, $metric_group) use ($metrics) {
                    if ($metric_group == 'none') {
                        $prefix = '';
                    } else {
                        $prefix = $metric_group . '_';
                    }

                    foreach ($metrics[$metric_group] as $metric_name => $value) {
                        $carry[$prefix . $metric_name] = $value;
                    }

                    return $carry;
                },
                []
            );
        }

        $progress = ProgressList::start('Metrics');
        foreach ($metrics as $metric_name => $value) {
            $value = (float) $value; // cast
            if (! isset($db_metrics[$metric_name])) {
                // insert new metric
                dbInsert(
                    [
                        'app_id' => $app['app_id'],
                        'metric' => $metric_name,
                        'value' => $value,
                    ],
                    'application_metrics'
                );
                $progress->mark('+');
            } elseif ($value != $db_metrics[$metric_name]['value']) {
                dbUpdate(
                    [
                        'value' => $value,
                        'value_prev' => $db_metrics[$metric_name]['value'],
                    ],
                    'application_metrics',
                    'app_id=? && metric=?',
                    [$app['app_id'], $metric_name]
                );
                $progress->mark('U');
            } else {
                $progress->mark('.');
            }

            unset($db_metrics[$metric_name]);
        }

        // remove no longer existing metrics (generally should not happen
        foreach ($db_metrics as $db_metric) {
            \App\Models\ApplicationMetric::where('app_id', $app['app_id'])
                ->where('metric', $db_metric['metric'])
                ->delete();
            $progress->mark('-');
        }
        $progress->end();
    }
}

/**
 * This is to make it easier polling apps. Also to help standardize around JSON.
 *
 * If the data has is in base64, it will be converted and then gunzipped.
 * https://github.com/librenms/librenms-agent/blob/master/utils/lnms_return_optimizer
 * May be used to convert output from extends to that via piping it through it.
 *
 * The required keys for the returned JSON are as below.
 *  version     - The version of the snmp extend script. Should be numeric and at least 1.
 *  error       - Error code from the snmp extend script. Should be > 0 (0 will be ignored and negatives are reserved)
 *  errorString - Text to describe the error.
 *  data        - An key with an array with the data to be used.
 *
 * If the app returns an error, an exception will be raised.
 * Positive numbers will be errors returned by the extend script.
 *
 * Possible parsing related errors:
 * -2 : Failed to fetch data from the device
 * -3 : Could not decode the JSON.
 * -4 : Empty JSON parsed, meaning blank JSON was returned.
 * -5 : Valid json, but missing required keys
 * -6 : Returned version is less than the min version.
 * -7 : Base64 decode failure.
 * -8 : Gzip decode failure.
 *
 * Error checking may also be done via checking the exceptions listed below.
 *   JsonAppPollingFailedException, -2        : Empty return from SNMP.
 *   JsonAppParsingFailedException, -3        : Could not parse the JSON.
 *   JsonAppBlankJsonException, -4            : Blank JSON.
 *   JsonAppMissingKeysException, -5          : Missing required keys.
 *   JsonAppWrongVersionException , -6        : Older version than supported.
 *   JsonAppExtendErroredException            : Polling and parsing was good, but the returned data has an error set.
 *                                              This may be checked via $e->getParsedJson() and then checking the
 *                                              keys error and errorString.
 *   JsonAppPollingBase64DecodeException , -7 : Base64 decoding failed.
 *   JsonAppPollingGzipDecodeException , -8   : Gzip decoding failed.
 * The error value can be accessed via $e->getCode()
 * The output can be accessed via $->getOutput() Only returned for code -3 or lower.
 * The parsed JSON can be access via $e->getParsedJson()
 *
 * All of the exceptions extend JsonAppException.
 *
 * If the error is less than -1, you can assume it is a legacy snmp extend script.
 *
 * @param  array  $device
 * @param  string  $extend  the extend name. For example, if 'zfs' is passed it will be converted to 'nsExtendOutputFull.3.122.102.115'.
 * @param  int  $min_version  the minimum version to accept for the returned JSON. default: 1
 * @return array The json output data parsed into an array
 *
 * @throws JsonAppBlankJsonException
 * @throws JsonAppExtendErroredException
 * @throws JsonAppMissingKeysException
 * @throws JsonAppParsingFailedException
 * @throws JsonAppPollingFailedException
 * @throws JsonAppWrongVersionException
 */
function json_app_get($device, $extend, $min_version = 1)
{
    $output = snmp_get($device, 'nsExtendOutputFull.' . Oid::encodeString($extend), '-Oqv', 'NET-SNMP-EXTEND-MIB');

    // save for returning if not JSON
    $orig_output = $output;

    // make sure we actually get something back
    if (empty($output)) {
        throw new JsonAppPollingFailedException('Empty return from snmp_get.', -2);
    }

    // checks for base64 decoding and converts it to non-base64 so it can gunzip
    if (preg_match('/^[A-Za-z0-9\/\+\n]+\=*\n*$/', $output) && ! preg_match('/^[0-9]+\n/', $output)) {
        $output = base64_decode($output);
        if (! $output) {
            if (Debug::isEnabled()) {
                echo "Decoding Base64 Failed...\n\n";
            }
            throw new JsonAppBase64DecodeException('Base64 decode failed.', $orig_output, -7);
        }
        $output = gzdecode($output);
        if (! $output) {
            if (Debug::isEnabled()) {
                echo "Decoding GZip failed...\n\n";
            }
            throw new JsonAppGzipDecodeException('Gzip decode failed.', $orig_output, -8);
        }
        if (Debug::isVerbose()) {
            echo 'Decoded Base64+GZip Output: ' . $output . "\n\n";
        }
    } else {
        $output = stripslashes($output);
        if (Debug::isVerbose()) {
            echo 'Output post stripslashes: ' . $output . "\n\n";
        }
    }

    //  turn the JSON into a array
    $parsed_json = json_decode($output, true);

    // improper JSON or something else was returned. Populate the variable with an error.
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new JsonAppParsingFailedException('Invalid JSON', $orig_output, -3);
    }

    // There no keys in the array, meaning '{}' was was returned
    if (empty($parsed_json)) {
        throw new JsonAppBlankJsonException('Blank JSON returned.', $output, -4);
    }

    // It is a legacy JSON app extend, meaning these are not set
    if (! isset($parsed_json['error'], $parsed_json['data'], $parsed_json['errorString'], $parsed_json['version'])) {
        throw new JsonAppMissingKeysException('Legacy script or extend error, missing one or more required keys.', $output, $parsed_json, -5);
    }

    if ($parsed_json['version'] < $min_version) {
        throw new JsonAppWrongVersionException("Script,'" . $parsed_json['version'] . "', older than required version of '$min_version'", $output, $parsed_json, -6);
    }

    if ($parsed_json['error'] != 0) {
        throw new JsonAppExtendErroredException("Script returned exception: {$parsed_json['errorString']}", $output, $parsed_json, $parsed_json['error']);
    }

    return $parsed_json;
}
