hide_toc: true

# Metric storage

By default we ship all metrics to RRD files, either directly or via
[RRDCached](RRDCached.md). On top of this  you can ship metrics to
Graphite, InfluxDB (v1 or v2 API), OpenTSDB or Prometheus. At present you can't use
these backends to display graphs within LibreNMS and will need to use
something like [Grafana](https://grafana.com/).

For further information on configuring LibreNMS to ship data to one of
the other backends then please see the documentation below.

- [Graphite](metrics/Graphite.md)
- [InfluxDB](metrics/InfluxDB.md)
- [InfluxDBv2](metrics/InfluxDBv2.md)
- [OpenTSDB](metrics/OpenTSDB.md)
- [Prometheus](metrics/Prometheus.md)
