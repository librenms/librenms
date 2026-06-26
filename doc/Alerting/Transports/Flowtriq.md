## Flowtriq

[Flowtriq](https://flowtriq.com) is a DDoS detection and traffic analytics platform. Sending LibreNMS alerts to Flowtriq lets you correlate infrastructure alerts (device down, interface errors, BGP session drops) with DDoS attack data and traffic anomalies on the same timeline.

To configure the transport, provide your Flowtriq webhook URL and, optionally, an API key for authentication.

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | https://app.flowtriq.com/api/webhooks/librenms |
| API Key | your-api-key |

For more information, see the [Flowtriq documentation](https://flowtriq.com/docs).
