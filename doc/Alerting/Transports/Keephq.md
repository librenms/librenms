# KeepHQ Transport

Send LibreNMS alerts to [KeepHQ](https://keephq.dev) via their webhook API.

## Configuration

| Config    | Description                     | Example                                                |
|-----------|---------------------------------|--------------------------------------------------------|
| API URL   | KeepHQ webhook endpoint         | `https://api.keephq.dev/alerts/event/libre_nms`        |
| API Key   | KeepHQ API key (role: webhook)  | `sk-abc123...`                                         |

## Setup

1. Generate a KeepHQ API key with the **webhook** role (Keep Dashboard → Settings → API Keys).
2. In LibreNMS, go to **Alerts → Alert Transports** and create a new **Keephq** transport.
3. Set the API URL to `https://api.keephq.dev/alerts/event/libre_nms` and paste your API key.
4. Assign the transport to an alert operation.

## Payload

The transport sends all alert fields as a JSON body with `Content-Type: application/json` and `X-API-KEY` authorization header. Nested objects (`faults`, `builder`, `rule`, `contacts`) are serialized as JSON objects — not stringified — so multiline text and special characters are handled correctly.

## References

- [KeepHQ — LibreNMS Provider](https://github.com/keephq/keep/tree/master/providers/libre_nms)
- [KeepHQ — Webhooks Integration](https://keephq.dev/docs/integrations/libre-nms)
