## AlertOps

The AlertOps integration forwards LibreNMS alerts to AlertOps with
detailed information. AlertOps is a dispatcher for LibreNMS alerts. It
selects the correct people or teams from the on-call schedules. It
sends the notifications by email, SMS, phone call, and mobile push
notification for iOS and Android devices. AlertOps also has escalation
policies. These policies control an alert until someone assigns or
closes it. You can also filter and combine alerts on different values.

To set up the integration:

- Create a LibreNMS integration. Sign up for an AlertOps account. Then create a LibreNMS integration on the integrations page. AlertOps then gives an inbound integration endpoint URL. Copy this URL to LibreNMS.
- Configure the LibreNMS integration. In LibreNMS, open the integration settings. Then paste the inbound integration URL from AlertOps.

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://url/path/to/webhook> |
