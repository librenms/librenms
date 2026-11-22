## Microsoft Teams

LibreNMS sends alerts to a Microsoft Teams channel through a **Power
Automate Workflow** webhook.

!!! note
    Microsoft retired the Office 365 Connectors in Teams and replaced them
    with Workflow webhooks. LibreNMS no longer builds a `MessageCard` for
    legacy connector URLs (`outlook.office.com`, `outlook.office365.com`
    or `*.webhook.office.com`). If you still use one, create a Workflow
    webhook and replace the URL in the transport settings. For the
    details, read the [Microsoft 365 Dev
    Blog](https://devblogs.microsoft.com/microsoft365dev/retirement-of-office-365-connectors-within-microsoft-teams/).

### Configuration

| Config | Description |
| --- | --- |
| Webhook URL | The full incoming webhook URL from Teams. This field is required |
| Use JSON | With this option on, LibreNMS sends the raw body of the alert template. The template must be valid JSON. With this option off, LibreNMS builds an `AdaptiveCard` from the alert title and the template text |

### Creating a Workflow webhook

1. Open the target Teams channel, click **`…`** → **Workflows**.
2. Find and select the template **"Send webhook alerts to a channel"**. *Teams no longer offers "Post to a channel when a webhook request is received".*
3. Obey the wizard. Then copy the generated webhook URL.
4. Paste the URL into the LibreNMS transport configuration.
5. Leave **Use JSON** off. LibreNMS then sends the alert title and the
   rendered template text as an `AdaptiveCard`.

With Use JSON off, HTML tags are removed from the template output because
an `AdaptiveCard` shows them as plain text. Markdown bold, italic, lists
and links still work. Teams rejects messages over 28 KB, so LibreNMS
cuts the alert text at 20000 bytes.

The workflow answers `HTTP 202` before it posts anything. If the post
fails later, LibreNMS can not see it. Check the workflow's run history in
Power Automate when alerts go missing.

!!! note
    A Workflow can not acknowledge an alert in LibreNMS. Calling the
    LibreNMS API from a Workflow needs the HTTP action, which requires a
    Power Automate Premium licence.

### Use JSON

With Use JSON on, LibreNMS sends the rendered template as the request
body, for every rule that uses this transport. For a Workflow webhook the
template has to produce valid JSON with the `message`/`attachments`
envelope.

- If the template outputs HTML or plain text, for example a template
  shared with email, the workflow rejects it with `HTTP 400` and nothing
  is posted. The failure shows up in the event log.
- The **Test** button sends a built-in `AdaptiveCard`, not your template.
  A passing test shows the workflow accepted the request. It does not
  show that the card was posted or that your template renders valid JSON.

### Alert templates

Markdown - JSON off:
[Alert Template Microsoft Teams - Markdown](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-markdown)

AdaptiveCard - JSON on:
[Alert Template Microsoft Teams - AdaptiveCard JSON](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-adaptivecard-json)

### Behaviour summary

| Use JSON | Result |
| --- | --- |
| Off | `AdaptiveCard` built from the alert title and text |
| On | Raw template body sent as-is (the template provides the envelope) |

### Example

| Config | Example |
| --- | --- |
| Webhook URL | `https://prod-12.westeurope.logic.azure.com/workflows/abc123.../triggers/manual/paths/invoke` |
| Use JSON | Unchecked (default) |

### References

- [Microsoft 365 Dev Blog — Retirement of Office 365 connectors within Microsoft Teams](https://devblogs.microsoft.com/microsoft365dev/retirement-of-office-365-connectors-within-microsoft-teams/)
- [Microsoft Learn — Webhooks and connectors](https://learn.microsoft.com/microsoftteams/platform/webhooks-and-connectors/what-are-webhooks-and-connectors)
