## PagerDuty

LibreNMS can make use of PagerDuty, this is done by utilizing an API
key and Integraton Key.

API Keys can be found under 'API Access' in the PagerDuty portal.

Integration Keys can be found under 'Integration' for the particular
Service you have created in the PagerDuty portal.

**Example:**

| Config | Example |
| ------ | ------- |
| API Key | randomsample |
| Integration Key | somerandomstring |

**Fixed LibreNMS -> PagerDuty field mappings**

| LibreNMS | PagerDuty |
| -------- | --------- |
| DeviceGroupName | payload.group |
| DeviceType | payload.class |
| Hostname | payload.source |
| Alert severity | payload.severity |
| Alert title | payload.summary |

**Nice formatting**

PagerDuty formats the Custom Details panel nicely if it receives valid JSON.
At the time of writing, the PagerDuty web UI handles nested arrays/objects correctly, but the mobile app still shows nested structures as strings.
