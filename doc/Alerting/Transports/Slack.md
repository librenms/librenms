## Slack

The Slack transport will POST the alert message to your Slack Incoming
WebHook using the attachments option, you are able to specify multiple
webhooks along with the relevant options to go with it. Simple html
tags are stripped from the message. All options are optional, the
only required value is for url, without this  then no call to Slack will be made.

We currently support the following attachment options:

- `author_name`

We currently support the following global message options:

- `channel_name` : Slack channel name (without the leading '#') to which the alert will go
- `icon_emoji` : Emoji name in colon format to use as the author icon

[Slack docs](https://api.slack.com/docs/message-attachments)

The alert template can make use of
[Slack markdown](https://api.slack.com/reference/surfaces/formatting#basic-formatting).
In the Slack markdown dialect, custom links are denoted with HTML angled
brackets, but LibreNMS strips these out. To support embedding custom links in alerts,
use the bracket/parentheses markdown syntax for links.  For example if you would
typically use this for a Slack link:

`<https://www.example.com|My Link>`

Use this in your alert template:

`[My Link](https://www.example.com)`

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | <https://slack.com/url/somehook> |
| Channel | network-alerts |
| Author Name | LibreNMS Bot |
| Icon | `:scream:` |