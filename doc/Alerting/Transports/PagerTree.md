## PagerTree

The PagerTree transport sends the alert message to your PagerTree
incoming webhook with a POST request. Only the PagerTree webhook
integration URL is necessary.

The transport maps these LibreNMS fields to PagerTree. It converts each
LibreNMS alert state to a PagerTree event type.

| LibreNMS alert state | PagerTree event_type |
| -------------------- | -------------------- |
| 0 (OK) | resolved |
| 1 (Alert) | create |
| 2 (Ack) | acknowledged |


| LibreNMS | PagerTree |
| -------- | --------- |
| Alert state | event_type |
| Alert ID | Id |
| Alert title | Title |
| Alert msg | Description |


To add the webhook in the PagerTree portal, select "Integrations" -->
"New Integration" --> "webhooks". The webhook URL has the label
"Endpoint" on the new PagerTree integration summary page.

[PagerTree Docs](https://pagertree.com/docs/integration-guides/webhook). 
[LibreNMS Alert Data](https://github.com/librenms/librenms/blob/master/LibreNMS/Alert/AlertData.php).
