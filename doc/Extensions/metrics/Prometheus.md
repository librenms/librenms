# Enabling support for Prometheus

Prometheus support is alpha quality. It has few tests and it is still
in development. It only sends the data to a Prometheus PushGateway. Use
this support at your own risk.

## Requirements (Older versions can work. We did not test them

- Prometheus >= 2.0
- PushGateway >= 0.4.0
- Grafana
- PHP-CURL

This document does not describe the setup of these components. We
cannot help with them.

## What you do not get

- Graphs. You need Grafana for this reason. Build your own graphs in
  Grafana.
- Support for Prometheus or Grafana.
  You need experience with these tools.

RRD continues to work in the normal way. LibreNMS therefore also
continues to work in the normal way.

## Configuration

!!! setting "poller/prometheus"
    ```bash
    lnms config:set prometheus.enable true
    lnms config:set prometheus.url 'http://127.0.0.1:9091'
    lnms config:set prometheus.job 'librenms'
    lnms config:set prometheus.prefix 'librenms'
    ```

If your pushgateway uses basic authentication, set these options:

!!! setting "poller/prometheus"
    ```bash
    lnms config:set prometheus.user username
    lnms config:set prometheus.password password
    ```

Additional settings

!!! setting "poller/prometheus"
    ```bash
    lnms config:set prometheus.attach_sysname true
    ```


## Prefix

The 'prefix' option puts its value at the start of each metric name.

Without this option, the metric names look like this:

```
OUTUCASTPKTS
ifOutUcastPkts_rate
INOCTETS
ifInErrors_rate
```

With the prefix 'librenms', the same metrics have these names:

```
librenms_OUTUCASTPKTS
librenms_ifOutUcastPkts_rate
librenms_INOCTETS
librenms_ifInErrors_rate
```

## Sample Prometheus Scrape Config (for scraping the Push Gateway)

```yml
- job_name: pushgateway
  scrape_interval: 300s
  honor_labels: true
  static_configs:
    - targets: ['127.0.0.1:9091']
```

LibreNMS sends the same data from rrd to Prometheus, and Prometheus records it.
You can then create graphs in Grafana for the information that you
need.
