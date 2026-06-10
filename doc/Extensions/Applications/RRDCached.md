# RRDCached

Install/Setup:
For Install/Setup Local Librenms RRDCached: Please see [RRDCached](../RRDCached.md)

Will collect stats by:
1. Connecting directly to the associated device on port 42217
2. Monitor thru snmp with SNMP extend, as outlined below
3. Connecting to the rrdcached server specified by the `rrdcached` setting

SNMP extend script to monitor your (remote) RRDCached via snmp

## SNMP Extend

1. Download the script onto the desired host
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/rrdcached -O /etc/snmp/rrdcached
```

2. Make the script executable
```
chmod +x /etc/snmp/rrdcached
```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend rrdcached /etc/snmp/rrdcached
```
