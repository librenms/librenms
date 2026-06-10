## Splunk

LibreNMS can send alerts to a Splunk instance and provide all device
and alert details.

Example output:

```
Feb 21 15:21:52 nms  hostname="localhost", sysName="localhost", 
sysDescr="", sysContact="", os="fortigate", type="firewall", ip="localhost", 
hardware="FGT_50E", version="v5.6.9", serial="", features="", location="", 
uptime="387", uptime_short=" 6m 27s", uptime_long=" 6 minutes 27 seconds", 
description="", notes="", alert_notes="", device_id="0", rule_id="0", 
id="0", proc="", status="1", status_reason="", ping_timestamp="", ping_loss="0", 
ping_min="25.6", ping_max="26.8", ping_avg="26.3", 
title="localhost recovered from  Device up/down  ", elapsed="14m 54s", uid="0", 
alert_id="0", severity="critical", name="Device up/down", 
timestamp="2020-02-21 15:21:33", state="0", device_device_id="0", 
device_inserted="", device_hostname="localhost", device_sysName="localhost", 
device_ip="localhost", device_overwrite_ip="", device_timeout="", device_retries="", 
device_snmp_disable="0", device_bgpLocalAs="0", 
device_sysObjectID="", device_sysDescr="", 
device_sysContact="", device_version="v5.6.9", device_hardware="FGT_50E", 
device_features="build1673", device_location_id="", device_os="fortigate", 
device_status="1", device_status_reason="", device_ignore="0", device_disabled="0", 
device_uptime="387", device_agent_uptime="0", device_last_polled="2020-02-21 15:21:33", 
device_last_poll_attempted="", device_last_polled_timetaken="7.9", 
device_last_discovered_timetaken="11.77", device_last_discovered="2020-02-21 13:16:42", 
device_last_ping="2020-02-21 15:21:33", device_last_ping_timetaken="26.3", 
device_purpose="", device_type="firewall", device_serial="FGT50EXXX", 
device_icon="images/os/fortinet.svg", device_poller_group="0", 
device_override_sysLocation="0", device_notes="", device_port_association_mode="1", 
device_max_depth="0", device_disable_notify="0", device_location="", 
device_vrf_lites="Array", device_lat="", device_lng="", - 
sysObjectID => ""; `
```

Each alert will be sent as a separate message.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| UDP Port | 514 |