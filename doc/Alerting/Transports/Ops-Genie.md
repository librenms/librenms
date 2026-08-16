## OpsGenie

> ⚠️ **Atlassian announced the end of life of Opsgenie on 5 April 2027.**
[Read more here](https://www.atlassian.com/blog/announcements/evolution-of-it-operations)

The OpsGenie integration forwards LibreNMS alerts with detailed
information. OpsGenie is a dispatcher for these alerts. It selects the
correct people from the on-call schedules. It notifies them by email,
SMS, phone call, and iOS or Android push notification. It then
escalates each alert until someone acknowledges or closes it.

Sign up. Then create a [LibreNMS
integration](https://docs.opsgenie.com/docs/librenms-integration) on
the integrations page. Then copy the API key from OpsGenie to LibreNMS.

To acknowledge and close alerts automatically, use the Marid
integration. The [OpsGenie LibreNMS integration
page](https://docs.opsgenie.com/docs/librenms-integration) gives more
details and screenshots.

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://url/path/to/webhook> |