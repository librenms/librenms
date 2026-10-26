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
2. Find and select the template **"Send webhook alerts to a channel"**. *Older versions of this page pointed to "Post to a channel when a webhook request is received", which Teams no longer offers.*
3. Obey the wizard and pick the team and channel. Then copy the generated webhook URL.
4. Paste the URL into the LibreNMS transport configuration.
5. Leave **Use JSON** off. Your existing alert templates then work without a change.

Posts show up as coming from the Workflows app, but the workflow runs
under the Teams connection of whoever created it. If that account is
disabled or its sign-in expires, the posts stop. Other transports are not
affected.

!!! note
    A Workflow can't acknowledge the alert in LibreNMS for you. Calling the
    LibreNMS API from a Workflow needs the HTTP action, which requires a
    Power Automate Premium licence. A simpler option is to put a link to the
    device's alerts page on the card, so people can acknowledge it in
    LibreNMS under their own account.

### Alert templates

MessageCard - JSON off:
[Alert Template Microsoft Teams - MessageCard Markdown](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-markdown)

MessageCard - JSON on:
[Alert Template Microsoft Teams - MessageCard JSON](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-json)

AdaptiveCard - JSON on:
[Alert Template Microsoft Teams - AdaptiveCard JSON](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-adaptivecard-json)

AdaptiveCard with a graph - JSON on:
[Alert Template Microsoft Teams - AdaptiveCard with a graph](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-adaptivecard-with-a-graph)

### Use JSON and shared templates

With Use JSON on, LibreNMS sends the rendered template as-is, for every
rule that uses this transport. The template has to produce valid JSON
with the `message`/`attachments` envelope.

If a rule uses the same template for email and Teams, you can check
`$alert->transport` (for example `mail` or `msteams`) and give each
transport its own output:

```php
@if ($alert->transport == 'msteams')
{!! json_encode(['type' => 'message', 'attachments' => [/* your card */]]) !!}
@else
...your email body, unchanged...
@endif
```

If you have more than one Teams transport, `$alert->transport_name` tells
them apart.

If the template outputs HTML or plain text instead, the workflow rejects
it with `HTTP 400 InvalidRequestContent` and nothing is posted. The
failure shows up in the device's event log. The rule's other transports
still deliver.

Build the card with `json_encode()` like the examples do, rather than
putting the JSON together by hand. That way quotes or new lines in fault
data or notes can't break it.

### Images and graphs

Teams fetches image URLs itself, so if your LibreNMS needs a login, graph
URLs won't load in the card. You can embed the image instead as a
`data:image/png;base64,...` URI, as long as the whole request stays under
about 28 KB. A 400 x 110 graph reduced to 32 colours comes out at around
10 KB. There is an [example
template](https://docs.librenms.org/Alerting/Templates/#microsoft-teams-adaptivecard-with-a-graph)
that does this.

When graphs are drawn inside the alert dispatcher, RRDtool can print
warnings such as `Fontconfig error: No writable cache directories`. That
ends up in the template output and breaks the JSON, so wrap the graph call
in `ob_start()` / `ob_end_clean()`. To get rid of the warning itself, give
the poller user a writable cache directory, e.g.
`XDG_CACHE_HOME=/tmp/.cache`.

### Behaviour summary

| URL type | Use JSON | Result |
| --- | --- | --- |
| Legacy `outlook.office.com` | Off | Bare `MessageCard` POST (unchanged) |
| Legacy `outlook.office.com` | On | Raw template body sent as-is |
| Workflow `*.logic.azure.com` | Off | `MessageCard` auto-wrapped in envelope |
| Workflow `*.logic.azure.com` | On | Raw template body sent as-is (user provides envelope) |
| Workflow `*.environment.api.powerplatform.com` | Off | `MessageCard` auto-wrapped in envelope |
| Workflow `*.environment.api.powerplatform.com` | On | Raw template body sent as-is (user provides envelope) |

### Example

| Config | Example |
| --- | --- |
| Webhook URL | `https://prod-12.westeurope.logic.azure.com/workflows/abc123.../triggers/manual/paths/invoke` |
| Use JSON | Unchecked (default) |

### References

- [Microsoft 365 Dev Blog — Retirement of Office 365 connectors within Microsoft Teams](https://devblogs.microsoft.com/microsoft365dev/retirement-of-office-365-connectors-within-microsoft-teams/)
- [Microsoft Learn — Webhooks and connectors](https://learn.microsoft.com/microsoftteams/platform/webhooks-and-connectors/what-are-webhooks-and-connectors)
- [Power Platform Community — MessageCard payload support confirmation](https://community.powerplatform.com/forums/thread/details/?threadid=915c0cfb-d5eb-f011-8544-000d3a554a74)
