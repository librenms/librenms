hide_toc: true

# Metric storage

By default, LibreNMS writes all metrics to RRD files. It writes them
directly or through [RRDCached](RRDCached.md). You can also send the
metrics to Graphite, InfluxDB (v1 or v2 API), OpenTSDB, or Prometheus.
LibreNMS cannot draw graphs from these backends. Use a tool such as
[Grafana](https://grafana.com/) for those graphs.

For the configuration of another backend, read the documents below.

- [Graphite](metrics/Graphite.md)
- [InfluxDB](metrics/InfluxDB.md)
- [InfluxDBv2](metrics/InfluxDBv2.md)
- [OpenTSDB](metrics/OpenTSDB.md)
- [Prometheus](metrics/Prometheus.md)
