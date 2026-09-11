## IBM On Call Manager (OCM)

LibreNMS connects to IBM On Call Manager with a webhook URL. You create
this URL when you add the LibreNMS integration.

The webhook URL has the name `ocm-url`. It is under 'Integrations' in
the IBM On Call Manager portal, after you select LibreNMS as the
integration.

IBM On Call Manager uses the webhook to send the name of the alert rule
and other details. These details are the name or the IP address of the
system, the name of the alert, the severity, the timestamp, the OS, the
location, and a unique ID. 

**Example:**

| Config  | Example                                  |
| ------- | ---------------------------------------- |
| ocm-url | https://ibm-ocm-webhook.example.com/api |

**Payload Example**:

```json
{
  "eventSource": {
    "name": "{{ $alert->sysName }}",
    "description": "{{ $alert->sysDescr }}",
    "displayName": "LibreNMS Alerts - DBAoC",
    "type": "server",
    "sourceID": "LibreNMS-DBAoC"
  },
  "resourceAffected": {
    "hostname": "{{ $alert->hostname }}",
    "ipAddress": "{{ $alert->ip }}",
    "os": "{{ $alert->os }}",
    "location": "{{ $alert->location }}",
    "component": "{{ $alert->sysName }}"
  },
  "eventInfo": {
    "summary": "{{ $alert->title }}",
    "msg": "{{ $alert->msg }}",
    "severity": "{{ $alert->severity }}",
    "timestamp": "{{ $alert->timestamp }}",
    "uniqueID": "{{ $alert->uid }}"
  }
}
```