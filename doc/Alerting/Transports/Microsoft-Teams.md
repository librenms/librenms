## Microsoft Teams

LibreNMS can send alerts to a Microsoft Teams channel using an Incoming Webhook. The transport supports both the **legacy Office 365 Connector** webhooks and the newer **Power Automate Workflow** webhooks, and automatically selects the correct payload format based on the webhook URL — no manual configuration is required.

!!! note
    Microsoft retired Office 365 Connectors in Teams and replaced them with Power Automate Workflow webhooks (retirement deadline: **April 30, 2026**). If you are still using a legacy connector URL (`outlook.office.com`, `outlook office365.com`, or `*.webhook.office.com`), migrate to a Workflow webhook before that date. See the [Microsoft 365 Dev Blog](https://devblogs.microsoft.com/microsoft365dev/retirement-of-office-365-connectors-within-microsoft-teams/) for details.

### Webhook types

The transport detects the webhook type automatically from the URL hostname and adjusts the payload accordingly:

| Webhook type | URL pattern | Payload sent (Use JSON?: OFF)) |
| --- | --- | --- |
| Legacy O365 Connector | `outlook.office.com/webhook/…` | Bare `MessageCard` JSON object |
| Legacy O365 Connector | `outlook.office365.com/webhook/…` | Bare `MessageCard` JSON object |
| Legacy O365 Connector (vanity) | `<company>.webhook.office.com/webhookb2/…` | Bare `MessageCard` JSON object |
| Workflow webhook (public/commercial) | `<region>.logic.azure.com/workflows/…` | `MessageCard` wrapped in `message`/`attachments` envelope |
| Workflow webhook (corporate/GCC) | `<default>.<region>.environment.api.powerplatform.com/powerautomate/…` | `MessageCard` wrapped in `message`/`attachments` envelope |

No configuration change is needed when migrating from a legacy connector URL to a Workflow webhook URL — simply replace the URL in the transport settings.

### Configuration

| Config | Description |
| --- | --- |
| Webhook URL | The full incoming webhook URL provided by Teams (required) |
| Use JSON | When enabled, the raw body of the alert template is sent as-is. Use this for Adaptive Card payloads. When disabled, LibreNMS builds a `MessageCard` from the alert data automatically |

### Creating a Workflow webhook

1. Open the target Teams channel, click **`…`** → **Workflows**.
2. Search for and select the template **"Post to a channel when a webhook request is received"**. *(Use this specific template — older "Send webhook alerts to a channel" templates do not support `MessageCard` payloads.)*
3. Follow the wizard, then copy the generated webhook URL.
4. Paste the URL into the LibreNMS transport configuration.
5. Leave **Use JSON** unchecked — your existing alert templates work without modification.

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
