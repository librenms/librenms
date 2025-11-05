# Metrics API

The Metrics API provides Prometheus-compatible metric endpoints for monitoring LibreNMS data. All endpoints return metrics in the Prometheus exposition format (text/plain) and require a valid API token with global-read access.

## Base Endpoint

### `metrics_index`

Get an overview of all available metrics endpoints.

Route: `/api/v0/metrics`

Input:
- None

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics
```

Output:
Returns an HTML page listing all available metrics endpoints with descriptions and usage examples.

## Device Filtering

Most metrics endpoints support optional query parameters to filter results to specific devices or device groups:

- `device_id` or `device_ids` — single or comma-separated device IDs
- `hostname` or `hostnames` — single or comma-separated hostnames (matches `hostname` and `sysName`)
- `device_group` — a device group id or name; the group will be expanded to its member devices

**Endpoints that support device filtering:**
- access_points, alerts, applications, customoids, devices, mempools, ports, ports_statistics, processors, sensors, services, storages, wireless_sensors

**Endpoints that do NOT support device filtering:**
- pollers (returns system-wide poller metrics only)

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' "https://foo.example/api/v0/metrics/ports?device_id=1,2,3"
curl -H 'X-Auth-Token: YOURAPITOKENHERE' "https://foo.example/api/v0/metrics/mempools?hostnames=sw1,sw2"
curl -H 'X-Auth-Token: YOURAPITOKENHERE' "https://foo.example/api/v0/metrics/sensors?device_group=4,5"
curl -H 'X-Auth-Token: YOURAPITOKENHERE' "https://foo.example/api/v0/metrics/devices?device_group=switches"
```

## Available Metrics Endpoints

### `metrics_access_points`

Get access point metrics.

Route: `/api/v0/metrics/access_points`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/access_points
```

Output:
Prometheus metrics for wireless access points including connection counts, signal strength, and other wireless-specific metrics.

### `metrics_alerts`

Get alert metrics.

Route: `/api/v0/metrics/alerts`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/alerts
```

Output:
Prometheus metrics for alerts including:
- `librenms_alerts_rules_total` — Total number of alert rules
- `librenms_alerts_total` — Total number of alert entries
- `librenms_alerts_state` — Alerts grouped by state (ok, alert, acknowledged)

Example metrics:
```
# HELP librenms_alerts_rules_total Total number of alert rules
# TYPE librenms_alerts_rules_total gauge
librenms_alerts_rules_total 25

# HELP librenms_alerts_total Total number of alerts rows
# TYPE librenms_alerts_total gauge
librenms_alerts_total 142

# HELP librenms_alerts_state Number of alerts by state
# TYPE librenms_alerts_state gauge
librenms_alerts_state{state="ok"} 120
librenms_alerts_state{state="alert"} 20
librenms_alerts_state{state="acknowledged"} 2
```

### `metrics_applications`

Get application metric values.

Route: `/api/v0/metrics/applications`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/applications
```

Output:
Prometheus metrics for application monitoring including:
- `librenms_applications_metrics_total` — Total number of application metrics
- `librenms_applications_metric` — Individual application metric values with labels for app_type, instance, and metric name

Example metrics:
```
# HELP librenms_applications_metrics_total Total number of application metrics rows
# TYPE librenms_applications_metrics_total gauge
librenms_applications_metrics_total 1543

# HELP librenms_applications_metric Application metric values
# TYPE librenms_applications_metric gauge
librenms_applications_metric{device_id="1",device_hostname="web01",device_sysName="web01.example.com",app_type="nginx",app_instance="default",metric="connections_active"} 42
librenms_applications_metric{device_id="1",device_hostname="web01",device_sysName="web01.example.com",app_type="nginx",app_instance="default",metric="requests_per_second"} 156.7
```

### `metrics_customoids`

Get custom OID metrics.

Route: `/api/v0/metrics/customoids`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/customoids
```

Output:
Prometheus metrics for custom SNMP OIDs configured in LibreNMS.

### `metrics_devices`

Get device-level metrics.

Route: `/api/v0/metrics/devices`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/devices
```

Output:
Prometheus metrics for devices including:
- `librenms_devices_total` — Total number of devices
- `librenms_devices_up` — Number of devices currently up
- `librenms_devices_down` — Number of devices currently down
- `librenms_devices_up` — Per-device status (1=up, 0=down)
- `librenms_devices_polled_timetaken_seconds` — Time taken for last poll
- `librenms_devices_discovered_timetaken_seconds` — Time taken for last discovery
- `librenms_devices_ping_timetaken_seconds` — Time taken for last ping
- `librenms_devices_uptime_seconds` — Device uptime in seconds

Example metrics:
```
# HELP librenms_devices_total Total number of devices
# TYPE librenms_devices_total gauge
librenms_devices_total 47

# HELP librenms_devices_up Number of devices currently up
# TYPE librenms_devices_up gauge
librenms_devices_up 45

# HELP librenms_devices_down Number of devices currently down
# TYPE librenms_devices_down gauge
librenms_devices_down 2

# HELP librenms_devices_up Device status (1=up, 0=down)
# TYPE librenms_devices_up gauge
librenms_devices_up{device_id="1",device_hostname="sw01",device_sysName="sw01.example.com",device_type="network"} 1
librenms_devices_up{device_id="2",device_hostname="fw01",device_sysName="fw01.example.com",device_type="firewall"} 1
```

### `metrics_mempools`

Get memory pool usage metrics.

Route: `/api/v0/metrics/mempools`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/mempools
```

Output:
Prometheus metrics for memory pool utilization including total, used, and free memory values.

### `metrics_pollers`

Get poller performance and cluster metrics.

Route: `/api/v0/metrics/pollers`

Input:
- None (no device filtering supported)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/pollers
```

Output:
Prometheus metrics for poller performance including polling times, queue depths, and cluster coordination metrics.

### `metrics_ports`

Get port metrics.

Route: `/api/v0/metrics/ports`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/ports
```

Output:
Prometheus metrics for network ports including:
- Octets (bytes) in/out
- Packets in/out
- Errors in/out
- Port status and administrative status
- Speed and duplex information

Example metrics:
```
# HELP librenms_ports_ifInOctets_total Port input octets
# TYPE librenms_ports_ifInOctets_total counter
librenms_ports_ifInOctets_total{device_id="1",device_hostname="sw01",port_id="1",ifName="GigabitEthernet0/1",ifAlias="uplink"} 1234567890

# HELP librenms_ports_ifOutOctets_total Port output octets  
# TYPE librenms_ports_ifOutOctets_total counter
librenms_ports_ifOutOctets_total{device_id="1",device_hostname="sw01",port_id="1",ifName="GigabitEthernet0/1",ifAlias="uplink"} 9876543210
```

### `metrics_ports_statistics`

Get higher-cardinality per-port statistics.

Route: `/api/v0/metrics/ports_statistics`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/ports_statistics
```

Output:
Detailed Prometheus metrics for ports with additional statistical data and higher cardinality labels.

### `metrics_processors`

Get processor usage metrics.

Route: `/api/v0/metrics/processors`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/processors
```

Output:
Prometheus metrics for CPU/processor utilization including usage percentages and load averages.

### `metrics_sensors`

Get health sensor metrics.

Route: `/api/v0/metrics/sensors`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/sensors
```

Output:
Prometheus metrics for hardware sensors including:
- Temperature sensors
- Power sensors  
- Voltage sensors
- Humidity sensors
- Fan speed sensors
- Current sensors

Example metrics:
```
# HELP librenms_sensors_temperature_celsius Temperature sensor readings in Celsius
# TYPE librenms_sensors_temperature_celsius gauge
librenms_sensors_temperature_celsius{device_id="1",device_hostname="sw01",sensor_id="1",sensor_descr="CPU Temperature",sensor_class="temperature"} 42.5

# HELP librenms_sensors_power_watts Power sensor readings in Watts
# TYPE librenms_sensors_power_watts gauge
librenms_sensors_power_watts{device_id="1",device_hostname="sw01",sensor_id="2",sensor_descr="PSU1 Power",sensor_class="power"} 150.3
```

### `metrics_services`

Get service check status metrics.

Route: `/api/v0/metrics/services`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/services
```

Output:
Prometheus metrics for service monitoring including service status, check results, and response times.

### `metrics_storages`

Get storage usage metrics.

Route: `/api/v0/metrics/storages`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/storages
```

Output:
Prometheus metrics for storage devices including disk usage, free space, and utilization percentages.

### `metrics_wireless_sensors`

Get wireless sensor metrics.

Route: `/api/v0/metrics/wireless_sensors`

Input:
- Standard device filtering parameters (optional)

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/metrics/wireless_sensors
```

Output:
Prometheus metrics for wireless-specific sensors including signal strength, noise levels, and wireless performance metrics.

## Authentication

All metrics endpoints require authentication via the `X-Auth-Token` header with a valid API token that has global-read access.

## Content Type

All metrics endpoints return data in the Prometheus exposition format with content type `text/plain; version=0; charset=utf-8`.

## Rate Limiting

Be mindful of the polling frequency when scraping these endpoints, especially for endpoints with high cardinality like ports and sensors. Consider using device filtering to reduce the scope of metrics collection for large deployments.

For Prometheus integration examples and configuration, see the [Prometheus Extensions documentation](../Extensions/metrics/Prometheus.md).