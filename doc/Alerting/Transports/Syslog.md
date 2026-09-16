## Syslog

LibreNMS can send alerts as syslog messages in the RFC 3164 format. For
more information about RFC 3164, read this page:
[https://tools.ietf.org/html/rfc3164](https://tools.ietf.org/html/rfc3164)

Example output: `<26> Mar 22 00:59:03 librenms.host.net librenms[233]:
[Critical] network.device.net: Port Down - port_id => 98939; ifDescr => xe-1/1/0;`

LibreNMS sends each fault as a separate syslog message.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| Port | 514 |
| Facility | 3 |