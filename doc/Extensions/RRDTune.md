# RRDTune

LibreNMS creates the rrd file of a port with a maximum value of
12500000000, that is 100G. A device with bad data can therefore show a
100M port at more than 40G. Such a value is impossible. The rrdtool
tune option corrects this. It sets the maximum value to the physical
speed of the interface, with a minimum of 10M.

There are three ways to enable this option:

- Globally under Global Settings -> Poller -> Datastore: RRDTool
- For the actual device, Edit Device -> Misc
- For each port, Edit Device -> Port Settings

LibreNMS then sets the maximum value at each change of the interface
speed. A physical change or a wrong report from the device causes such
a change. To set the values immediately, run the supplied script:

`lnms port:tune <hostname> <ifName>` 

The `*` wildcard is valid. The ifName is optional. For example:

`lnms port:tune local* eth*`

The script then runs the rrdtool tune on each port. It uses the ifSpeed
of that port.

For the help page, run `lnms port:tune -h`.
