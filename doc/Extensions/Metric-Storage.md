source: Extensions/Metric-Storage.md
hide_toc: true

### Metric storage
By default we ship all metrics to RRD files, either directly or via [RRDCached](RRDCached.md). On top of this 
you can ship metrics to InfluxDB, Graphite and / or OpenTSDB. At present you can't use these backends to display 
graphs within LibreNMS and will need to use something like [Grafana](https://grafana.com/).

For further information on configuring LibreNMS to ship data to one of the other backends then please see 
the documentation below.

>  - [InfluxDB](metrics/InfluxDB.md)
>  - [Graphite](metrics/Graphite.md)
>  - [OpenTSDB](metrics/OpenTSDB.md)
