## Jira Service Management

The Jira Service Management integration forwards LibreNMS alerts with
detailed information. Jira Service Management is a dispatcher for these
alerts. It selects the correct people from the on-call schedules. It
notifies them by email, SMS, phone call, and iOS or Android push
notification. It then escalates each alert until someone acknowledges
or closes it.

:warning: If this feature is not available on your site, check Jira
Service Management for updates.

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://url/path/to/webhook> |