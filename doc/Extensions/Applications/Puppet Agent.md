# Puppet Agent

SNMP extend script to get your Puppet Agent data into your host.

### SNMP Extend

1. Download the script onto the desired host
```
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/puppet_agent.py -O /etc/snmp/puppet_agent.py
```

2. Make the script executable
```
chmod +x /etc/snmp/puppet_agent.py
```

3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
```
extend puppet-agent /etc/snmp/puppet_agent.py
```

The Script needs `python3-yaml` package to be installed.

Per default script searches for on of this files:

* /var/cache/puppet/state/last_run_summary.yaml
* /opt/puppetlabs/puppet/cache/state/last_run_summary.yaml

optionally you can add a specific summary file with creating `/etc/snmp/puppet.json`
```
{
     "agent": {
        "summary_file": "/my/custom/path/to/summary_file"
     }
}
```
custom summary file has highest priority

4. Restart snmpd on the host
