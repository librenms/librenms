## OpsGenie

> ⚠️ **Atlassian have announced the EOL of Opsgenie on the 5th April 2027.
[Read more here](https://www.atlassian.com/blog/announcements/evolution-of-it-operations)

Using OpsGenie LibreNMS integration, LibreNMS forwards alerts to
OpsGenie with detailed information. OpsGenie acts as a dispatcher for
LibreNMS alerts, determines the right people to notify based on
on-call schedules and notifies via email, text messages (SMS), phone
calls and iOS & Android push notifications. Then escalates alerts
until the alert is acknowledged or closed.

Create a [LibreNMS
Integration](https://docs.opsgenie.com/docs/librenms-integration) from
the integrations page  once you signup. Then copy the API key from OpsGenie to LibreNMS.

If you want to automatically ack and close alerts, leverage Marid
integration. More detail with screenshots is available in
[OpsGenie LibreNMS Integration page](https://docs.opsgenie.com/docs/librenms-integration).

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://url/path/to/webhook> |