# Enabling support for VictoriaMetrics

VictoriaMetrics is a fast and easy-to-use time series database. It is compatible with Prometheus. LibreNMS can export metrics to VictoriaMetrics using the Prometheus text exposition format.

## Requirements

- VictoriaMetrics
- Grafana (for visualization)
- PHP-CURL

The setup of VictoriaMetrics and Grafana is out of scope for this documentation. Please refer to the [VictoriaMetrics documentation](https://docs.victoriametrics.com/) for installation and configuration.

RRD will continue to function as normal, so LibreNMS itself will continue to operate normally.

## Configuration

### Basic Configuration

!!! setting "poller/victoriametrics"
    ```bash
    lnms config:set victoriametrics.enable true
    lnms config:set victoriametrics.url 'http://127.0.0.1:8428'
    lnms config:set victoriametrics.prefix 'librenms'
    ```

### Authentication

If your VictoriaMetrics instance requires basic authentication:

!!! setting "poller/victoriametrics"
    ```bash
    lnms config:set victoriametrics.user username
    lnms config:set victoriametrics.password password
    ```

## Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `victoriametrics.enable` | Enable VictoriaMetrics integration | `false` |
| `victoriametrics.url` | VictoriaMetrics server URL | `http://127.0.0.1:8428` |
| `victoriametrics.prefix` | Optional text to prepend to exported metric names | `''` |
| `victoriametrics.user` | Username for basic authentication | `''` |
| `victoriametrics.password` | Password for basic authentication | `''` |
| `victoriametrics.attach_sysname` | Attach device sysName to metrics | `false` |

### Additional Options

#### Attach sysName

To include the device's sysName in the exported metrics:

!!! setting "poller/victoriametrics"
    ```bash
    lnms config:set victoriametrics.attach_sysname true
    ```

#### Prefix

Setting the 'prefix' option will cause all metric names to begin with the configured value.

setting the prefix to `librenms` will result in metric names like `librenms_ifInOctets`.
