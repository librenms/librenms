## Syslog

You can have LibreNMS emit alerts as syslogs complying with RFC 3164.

More information on RFC 3164 can be found here:
[https://tools.ietf.org/html/rfc3164](https://tools.ietf.org/html/rfc3164)

Example output: `<26> Mar 22 00:59:03 librenms.host.net librenms[233]:
[Critical] network.device.net: Port Down - port_id => 98939; ifDescr => xe-1/1/0;`

Each fault will be sent as a separate syslog.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| Port | 514 |
| Facility | 3 |