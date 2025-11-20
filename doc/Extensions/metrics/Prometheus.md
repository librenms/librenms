# Prometheus Integration

LibreNMS provides two methods for integrating with Prometheus:

1. **[Scrape](#scrape)** - Prometheus scrapes metrics directly from metrics endpoints in the LibreNMS API
2. **[Push Gateway](#push-gateway)** - LibreNMS pushes metrics to a Prometheus Push Gateway during polling

## Scrape

The Scrape method allows Prometheus to directly scrape metrics from LibreNMS using dedicated metrics API endpoints. This deviates from how most of the metrics exporters function. The upside is that this method is not tied to LibreNMS polling, and therefore doesn't increase polling time/load. It does however involve Prometheus pulling large amounts of data from the LibreNMS web server which queries the LibreNMS database. Please test to make sure these systems are up to the task before scraping all endpoints.

### Requirements

- Prometheus >= 2.0
- LibreNMS API token with global-read access
- Network connectivity from Prometheus to LibreNMS

### Available Metrics Endpoints

LibreNMS provides multiple metrics endpoints for different types of data:

- `/api/v0/metrics/devices` — Device-level metrics (status, uptime, polling times)
- `/api/v0/metrics/ports` — Network port metrics (traffic, errors, status)
- `/api/v0/metrics/sensors` — Hardware sensor data (temperature, power, etc.)
- `/api/v0/metrics/alerts` — Alert system metrics
- `/api/v0/metrics/applications` — Application monitoring data
- `/api/v0/metrics/mempools` — Memory usage metrics
- `/api/v0/metrics/processors` — CPU utilization metrics
- `/api/v0/metrics/storages` — Storage usage metrics
- `/api/v0/metrics/services` — Service check results
- And more...

For complete API documentation, see [Metrics API](../../API/Metrics.md).

### Authentication

Create an API token in LibreNMS:

1. Navigate to `/api-access/` in your LibreNMS web interface
2. Click 'Create API access token'
3. Select a user with global-read access
4. Enter a description (e.g., "Prometheus scraping")
5. Click 'Create API Token'
6. Save the generated token securely

### Device Filtering

All metrics endpoints except pollers support filtering to specific devices or device groups.  The pollers endpoint is for monitoring of LibreNMS itself, not monitored devices.

```yaml
# Scrape only specific devices
metrics_path: /api/v0/metrics/ports
params:
  device_ids: ['1,2,3']

# Scrape devices by hostname
metrics_path: /api/v0/metrics/sensors
params:
  hostnames: ['sw01,sw02,fw01']

# Scrape devices in a group
metrics_path: /api/v0/metrics/devices
params:
  device_group: ['core-switches']
```

### Prometheus Configuration

Configure your `prometheus.yml` to scrape LibreNMS metrics:

#### Basic Configuration

```yaml
global:
  scrape_interval: 60s

scrape_configs:
  # Device-level metrics
  - job_name: 'librenms-devices'
    static_configs:
      - targets: ['your.librenms.example:443']
    scheme: https
    metrics_path: /api/v0/metrics/devices
    headers:
      X-Auth-Token: 'YOURAPITOKENHERE'
    scrape_interval: 150s

  # Port/interface metrics
  - job_name: 'librenms-ports'
    static_configs:
      - targets: ['your.librenms.example:443']
    scheme: https
    metrics_path: /api/v0/metrics/ports
    headers:
      X-Auth-Token: 'YOURAPITOKENHERE'
    scrape_interval: 150s

  # Hardware sensor metrics
  - job_name: 'librenms-sensors'
    static_configs:
      - targets: ['your.librenms.example:443']
    scheme: https
    metrics_path: /api/v0/metrics/sensors
    headers:
      X-Auth-Token: 'YOURAPITOKENHERE'
    scrape_interval: 150s

  # Alert metrics
  - job_name: 'librenms-alerts'
    static_configs:
      - targets: ['your.librenms.example:443']
    scheme: https
    metrics_path: /api/v0/metrics/alerts
    headers:
      X-Auth-Token: 'YOURAPITOKENHERE'
    scrape_interval: 150s
```

#### Advanced Configuration with Filtering

```yaml
scrape_configs:
  # Core network devices only
  - job_name: 'librenms-core-devices'
    static_configs:
      - targets: ['your.librenms.example:443']
    scheme: https
    metrics_path: /api/v0/metrics/devices
    params:
      device_group: ['core-switches', 'core-routers']
    headers:
      X-Auth-Token: 'YOURAPITOKENHERE'
    scrape_interval: 150s

  # Application metrics
  - job_name: 'librenms-applications'
    static_configs:
      - targets: ['your.librenms.example:443']
    scheme: https
    metrics_path: /api/v0/metrics/applications
    headers:
      X-Auth-Token: 'YOURAPITOKENHERE'
    scrape_interval: 150s
```

### Scrape Intervals

If you scrape at the exact interval that your poller is polling at, 5 minutes (300 seconds) by default, you'll likely find that you get anomalies in your data. The reason is that due to poller drift, every once in a while you'll have instances where Prometheus scrapes data that hasn't yet been updated so the next scrape ends up with twice the data.

Example:
```
12:00:00 - Poller write 500
12:00:01 - Prometheus scrapes 500
12:05:00 - Poller writes 600
12:05:01 - Prometheus scrapes 600
12:10:01 - Prometheus scrapes 600
12:10:20 - Poller writes 700
12:15:00 - Poller writes 800
12:15:01 - Prometheus scrapes 800
```

So the data series in Prometheus is 500,600,600,800 which will produce a inaccurate result when calculated with a rate function.

You can overcome this by scraping more frequently, like half the polling duration, 150 seconds.

Example:
```
12:00:00 - Poller write 500
12:00:01 - Prometheus scrapes 500
12:02:31 - Prometheus scrapes 500
12:05:00 - Poller writes 600
12:05:01 - Prometheus scrapes 600
12:07:31 - Prometheus scrapes 600
12:10:01 - Prometheus scrapes 600
12:10:20 - Poller writes 700
12:12:31 - Prometheus scrapes 700
12:15:00 - Poller writes 800
12:15:01 - Prometheus scrapes 800
12:17:31 - Prometheus scrapes 800
```

So a series of 500,500,600,600,600,700,800,800.  So if you run your rate function against the data with a 5 minutes step interval, all polls will be accounted for.

#### Device Filtering

Use device filtering to reduce metric volume and improve performance:

```yaml
# Instead of scraping all devices
metrics_path: /api/v0/metrics/ports

# Filter to specific device groups
metrics_path: /api/v0/metrics/ports
params:
  device_group: ['production-switches']
```

### Example Metrics

#### Device Status
```
librenms_devices_up{device_id="1",device_hostname="sw01",device_type="network"} 1
librenms_devices_uptime_seconds{device_id="1",device_hostname="sw01"} 8640000
```

#### Port Traffic
```
librenms_ports_ifInOctets_total{device_id="1",port_id="1",ifName="GigabitEthernet0/1"} 1234567890
librenms_ports_ifOutOctets_total{device_id="1",port_id="1",ifName="GigabitEthernet0/1"} 9876543210
```

#### Temperature Sensors
```
librenms_sensors_temperature_celsius{device_id="1",sensor_id="1",sensor_descr="CPU Temperature"} 42.5
```

## Push Gateway

The Push Gateway method configures LibreNMS to push metrics to a Prometheus Push Gateway during polling. The Push Gateway then acts as an intermediary that Prometheus scrapes.

> **Warning**: Prometheus Push Gateway support is considered experimental. It hasn't been extensively tested and is still in development. Use at your own risk!

### Requirements

(Older versions may work but haven't been tested)

- Prometheus >= 2.0
- Prometheus Push Gateway >= 0.4.0  
- Grafana (for visualization)
- PHP-CURL

The setup of Prometheus, Push Gateway, and Grafana is out of scope for this documentation.

### Limitations

- No built-in visualizations - you need to create your own dashboards in Grafana
- Limited support - this integration is experimental
- RRD storage continues to function normally alongside Prometheus export

### Configuration

Enable Push Gateway integration in LibreNMS:

!!! setting "poller/prometheus"
    ```bash
    lnms config:set prometheus.enable true
    lnms config:set prometheus.url 'http://127.0.0.1:9091'
    lnms config:set prometheus.job 'librenms'
    lnms config:set prometheus.prefix 'librenms'
    ```

If your Push Gateway uses basic authentication, configure the following:

!!! setting "poller/prometheus"
    ```bash
    lnms config:set prometheus.user username
    lnms config:set prometheus.password password
    ```

### Metric Prefix

Setting the 'prefix' option will cause all metric names to begin with the configured value.

For instance without setting this option metric names will be something like this:

```
OUTUCASTPKTS
ifOutUcastPkts_rate
INOCTETS
ifInErrors_rate
```

Configuring a prefix name, for example 'librenms', instead causes those metrics to be exposed with the following names:

```
librenms_OUTUCASTPKTS
librenms_ifOutUcastPkts_rate
librenms_INOCTETS
librenms_ifInErrors_rate
```

### Prometheus Configuration for Push Gateway

Configure Prometheus to scrape the Push Gateway:

```yaml
scrape_configs:
  - job_name: pushgateway
    scrape_interval: 300s
    honor_labels: true
    static_configs:
      - targets: ['127.0.0.1:9091']
```

The same data stored in RRD will be sent to Prometheus and recorded. You can then create graphs within Grafana to display the information you need.
