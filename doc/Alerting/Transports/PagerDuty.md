## PagerDuty

LibreNMS works with PagerDuty. It uses an API key and an integration
key.

The API keys are under 'API Access' in the PagerDuty portal. The
integration keys are under 'Integration' of your service in the
PagerDuty portal.

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

PagerDuty formats the Custom Details panel correctly for valid JSON.
The PagerDuty web interface shows nested arrays and objects correctly.
The mobile app still shows a nested structure as a string.
