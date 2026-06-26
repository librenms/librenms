# Features

Here's a brief list of supported features, some might be missing. If
you think something is missing, feel free to ask us.

* Auto discovery
* Alerting
* Multiple environment sensors support
* Multiple protocols data collection (STP, OSPF, OSPFv3, BGP etc)
* VLAN, ARP and FDB table collection
* Customizable Dashboards
* Device Backup integration (Oxidized, RANCID)
* Distributed Polling
* Multiple Authentication Methods (MySQL, LDAP, Active Directory, HTTP)
* NetFlow, sFlow, IPFIX (NfSen)
* Service monitoring (Nagios Plugins)
* Syslog (Integrated, Graylog)
* Traffic Billing (Quota, 95th Percentile)
* Two Factor Authentication
* API
* Auto Updating

## Supported Vendors

Here's a list of supported vendors, some might be missing.
If you are unsure of whether your device is supported or not, feel free to ask us.

```sh exec="1"
grep -h "^text: " resources/definitions/os_detection/*.yaml \
| sed -E "s/^text: *[\"']?([^\"']+).*/\1/" \
| sort -f -u \
| awk '{\
  if (last != tolower(substr($0, 0, 1))) {\
    print "\n### "toupper(substr($0,0,1))"\n* "$0; last = tolower(substr($1, 0, 1))\
  } else {\
    print "* "$0\
  }\
}'
```
