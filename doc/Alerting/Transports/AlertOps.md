## AlertOps

Using AlertOps integration with LibreNMS, you can seamlessly forward alerts to AlertOps with detailed information. AlertOps acts as a dispatcher for LibreNMS alerts, allowing you to determine the right individuals or teams to notify based on on-call schedules. Notifications can be sent via various channels including email, text messages (SMS), phone calls, and mobile push notifications for iOS & Android devices. Additionally, AlertOps provides escalation policies to ensure alerts are appropriately managed until they are assigned or closed. You can also filter out/aggregate alerts based on different values.

To set up the integration:

- Create a LibreNMS Integration: Sign up for an AlertOps account and create a LibreNMS integration from the integrations page. This will generate an Inbound Integration Endpoint URL that you'll need to copy to LibreNMS.

- Configure LibreNMS Integration: In LibreNMS, navigate to the integration settings and paste the inbound integration URL obtained from AlertOps.

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://url/path/to/webhook> |
