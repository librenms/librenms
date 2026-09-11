## Microsoft Teams

LibreNMS sends alerts to a Microsoft Teams channel through an incoming
webhook. The transport accepts the **legacy Office 365 Connector**
webhooks and the newer **Power Automate Workflow** webhooks. It selects
the correct payload format from the webhook URL. No manual
configuration is necessary.

!!! note
    Microsoft removed the Office 365 Connectors from Teams and replaced
    them with Power Automate Workflow webhooks. The retirement deadline is
    **April 30, 2026**. If you still use a legacy connector URL
    (`outlook.office.com`, `outlook.office365.com`, or
    `*.webhook.office.com`), move to a Workflow webhook before that date.
    For the details, read the [Microsoft 365 Dev
    Blog](https://devblogs.microsoft.com/microsoft365dev/retirement-of-office-365-connectors-within-microsoft-teams/).

### Webhook types

The transport detects the webhook type from the URL hostname. It then
sets the correct payload:

| Webhook type | URL pattern | Payload sent (Use JSON?: OFF)) |
| --- | --- | --- |
| Legacy O365 Connector | `outlook.office.com/webhook/…` | Bare `MessageCard` JSON object |
| Legacy O365 Connector | `outlook.office365.com/webhook/…` | Bare `MessageCard` JSON object |
| Legacy O365 Connector (vanity) | `<company>.webhook.office.com/webhookb2/…` | Bare `MessageCard` JSON object |
| Workflow webhook (public/commercial) | `<region>.logic.azure.com/workflows/…` | `MessageCard` wrapped in `message`/`attachments` envelope |
| Workflow webhook (corporate/GCC) | `<default>.<region>.environment.api.powerplatform.com/powerautomate/…` | `MessageCard` wrapped in `message`/`attachments` envelope |

A move from a legacy connector URL to a Workflow webhook URL needs no
configuration change. Replace the URL in the transport settings.

### Configuration

| Config | Description |
| --- | --- |
| Webhook URL | The full incoming webhook URL from Teams. This field is required |
| Use JSON | With this option on, LibreNMS sends the raw body of the alert template. Use it for Adaptive Card payloads. With this option off, LibreNMS builds a `MessageCard` from the alert data |

### Creating a Workflow webhook

1. Open the target Teams channel, click **`…`** → **Workflows**.
2. Find and select the template **"Post to a channel when a webhook request is received"**. *Use this template. The older "Send webhook alerts to a channel" templates do not accept `MessageCard` payloads.*
3. Obey the wizard. Then copy the generated webhook URL.
4. Paste the URL into the LibreNMS transport configuration.
5. Leave **Use JSON** off. Your existing alert templates then work without a change.

### Alert templates

MessageCard - JSON off:
[Alert Template Microsoft Teams - MessageCard Markdown](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-markdown)

MessageCard - JSON on:
[Alert Template Microsoft Teams - MessageCard JSON](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-json)

AdaptiveCard - JSON on:
[Alert Template Microsoft Teams - AdaptiveCard JSON](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-adaptivecard-json)

### Behaviour summary

| URL type | Use JSON | Result |
| --- | --- | --- |
| Legacy `outlook.office.com` | Off | Bare `MessageCard` POST (unchanged) |
| Legacy `outlook.office.com` | On | Raw template body sent as-is |
| Workflow `*.logic.azure.com` | Off | `MessageCard` auto-wrapped in envelope |
| Workflow `*.logic.azure.com` | On | Raw template body sent as-is (user provides envelope) |

### Example

| Config | Example |
| --- | --- |
| Webhook URL | `https://prod-12.westeurope.logic.azure.com/workflows/abc123.../triggers/manual/paths/invoke` |
| Use JSON | Unchecked (default) |

### References

- [Microsoft 365 Dev Blog — Retirement of Office 365 connectors within Microsoft Teams](https://devblogs.microsoft.com/microsoft365dev/retirement-of-office-365-connectors-within-microsoft-teams/)
- [Microsoft Learn — Webhooks and connectors](https://learn.microsoft.com/microsoftteams/platform/webhooks-and-connectors/what-are-webhooks-and-connectors)
- [Power Platform Community — MessageCard payload support confirmation](https://community.powerplatform.com/forums/thread/details/?threadid=915c0cfb-d5eb-f011-8544-000d3a554a74)
