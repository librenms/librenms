## SIGNL4

SIGNL4 offers critical alerting, incident response and service dispatching for operating critical infrastructure. It alerts you persistently via app push, SMS text, voice calls, and email including tracking, escalation, on-call duty scheduling and collaboration.

Integrating SIGNL4 with LibreNMS to forward critical alerts with detailed information to responsible people or on-call teams. The integration supports triggering as well as closing alerts.

In the configuration for your SIGNL4 alert transport you just need to enter your SIGNL4 webhook URL including team or integration secret.

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | https://connect.signl4.com/webhook/{team-secret} |

You can find more information about the integration [here](https://docs.signl4.com/integrations/librenms/librenms.html).