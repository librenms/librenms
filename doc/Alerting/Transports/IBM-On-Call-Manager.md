## IBM On Call Manager (OCM)

LibreNMS can integrate with IBM On Call Manager by using a webhook URL you create by adding the LibreNMS integration.

The webhook URL (referred to as `ocm-url`) can be found under 'Integrations' in the IBM On Call Manager portal after selecting LibreNMS as the integration.

IBM On Call Manager uses the webhook to send the name of the alert rule, along with other relevant details. It will include the name or IP address of the system sending the alert, the name of the alert, the severity, timestamp, OS, location, and a unique ID. 

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