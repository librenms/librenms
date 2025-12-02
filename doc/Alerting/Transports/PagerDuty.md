## PagerDuty

LibreNMS can make use of PagerDuty, this is done by utilizing an API
key and Integraton Key.

API Keys can be found under 'API Access' in the PagerDuty portal.

Integration Keys can be found under 'Integration' for the particular
Service you have created in the PagerDuty portal.

**Example:**

| Config | Example |
| ------ | ------- |
| API Key | randomsample |
| Integration Key | somerandomstring |

**Fixed LibreNMS -> PagerDuty field mappings**

| LibreNMS | PagerDuty |
| -------- | --------- |
| DeviceGroupName | payload.group |
| DeviceType | payload.class |
| Hostname | payload.source |
| Alert severity | payload.severity |
| Alert title | payload.summary |

**Nice formatting**

PagerDuty formats the Custom Details panel nicely if it receives valid JSON.
At the time of writing, the PagerDuty web UI handles nested arrays/objects correctly, but the mobile app still shows nested structures as strings.

*Alert template example:*
```
@php
    $payload = [];

    // Basic fields
    $payload['Alert Title']  = $alert->title;
    $payload['Severity']     = $alert->severity;
    $payload['Hostname']     = $alert->hostname;
    $payload['IP']           = $alert->ip;
    $payload['Display name'] = $alert->display;
    $payload['Timestamp']    = $alert->timestamp;
    $payload['Rule']         = $alert->name ?: $alert->rule;
    $payload['Location']     = $alert->location;
    // Time elapsed
    if ($alert->state == 0 && !empty($alert->elapsed)) {
        $payload['Time elapsed'] = $alert->elapsed;
    }
    // Device groups
    if (!empty($alert->device_groups)) {
        $groups = [];
        foreach ($alert->device_groups as $key => $value) {
            $groups["#{$key}"] = $value;
        }
        if (!empty($groups)) {
            $payload['Device groups'] = $groups;
        }
    }
    // Faults
    if ($alert->faults) {
        $faults = [];
        // Loop through faults
        foreach ($alert->faults as $key => $value) {
            $fault = [];

            if (!empty($value['status_reason'])) {
                $fault['Status reason'] = $value['status_reason'];
            }

            if (!empty($value['sensor_class']) && isset($value['sensor_current'])) {
                $unit = __("sensors.{$value['sensor_class']}.unit");

                if (isset($value['sensor_limit']) && $value['sensor_current'] > $value['sensor_limit']) {
                    $fault['Sensor limit']      = $value['sensor_limit'];
                    $fault['Sensor over limit'] = round($value['sensor_current'] - $value['sensor_limit'], 2) . $unit;
                }

                if (isset($value['sensor_limit_low']) && $value['sensor_current'] < $value['sensor_limit_low']) {
                    $fault['Sensor lower limit']  = $value['sensor_limit_low'];
                    $fault['Sensor under limit']  = round($value['sensor_limit_low'] - $value['sensor_current'], 2) . $unit;
                }
            }

            // Only add fault if it has at least one field
            if (!empty($fault)) {
                $payload["Fault: {$key}"] = $fault;
            } else {
                $payload["Fault: {$key}"] = "Faults found but nothing was appended in the foreach loop, check alert template";
            }
        }
    }
@endphp
{!! json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
```
