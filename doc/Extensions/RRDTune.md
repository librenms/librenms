# RRDTune?

When we create rrd files for ports, we currently do so with a max value of 12500000000 (100G). Because of this if a device sends us bad data back then it can appear as though 
a 100M port is doing 40G+ which is impossible. To counter this you can enable the rrdtool tune option which will fix the max value to the interfaces physical speed (minimum of 10M).

To enable this you can do so in three ways!

 - Globally under Global Settings -> External Settings -> RRDTool Setup
 - For the actual device, Edit Device -> Misc
 - For each port, Edit Device -> Port Settings

Now when a port interface speed changes (this can happen because of a physical change or just because the device has mis-reported) the max value is set. If you don't want to wait until 
a port speed changes then you can run the included script:

script/tune_port.php -h <hostname> -p <ifName>

Wildcards are supported using *, i.e:

script/tune_port.php -h local* -p eth*

This script will then perform the rrdtool tune on each port found using the provided ifSpeed for that port.
