source: Extensions/metrics/Prometheus.md
path: blob/master/doc/

# Enabling support for Prometheus

Please be aware Prometheus support is alpha at best, It hasn't been
extensively tested and is still in development All it provides is the
sending of data to a a Prometheus PushGateway. Please be careful when
enabling this support you use it at your own risk!

## Requirements (Older versions may work but haven't been tested

- Prometheus >= 2.0
- PushGateway >= 0.4.0
- Grafana
- PHP-CURL

The setup of the above is completely out of scope here and we aren't
really able to provide any help with this side of things.

## What you don't get

- Pretty graphs, this is why at present you need Grafana. You need to
  build your own graphs within Grafana.
- Support for Prometheus or Grafana, we would highly recommend that
  you have some level of experience with these.

RRD will continue to function as normal so LibreNMS itself should
continue to function as normal.

## Configuration

```php
$config['prometheus']['enable'] = true;
$config['prometheus']['url'] = 'http://127.0.0.1:9091';
$config['prometheus']['job'] = 'librenms'; # Optional
$config['prometheus']['prefix'] = 'librenms'; # Optional
```

## Prefix

Setting the 'prefix' option will cause all metric names to begin with 
the configured value.

For instance without setting this option metric names will be something 
like this:

```
OUTUCASTPKTS
ifOutUcastPkts_rate
INOCTETS
ifInErrors_rate
```

Configuring a prefix name, for example 'librenms', instead caused those 
metrics to be exposed with the following names:

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

The same data then stored within rrd will be sent to Prometheus and
recorded. You can then create graphs within Grafana to display the
information you need.
