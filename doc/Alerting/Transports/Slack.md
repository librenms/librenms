## Slack

The Slack transport sends the alert message to your Slack incoming
webhook with a POST request. It uses the attachments option. You can
give several webhooks, each with its own options. LibreNMS removes the
simple HTML tags from the message. All options are optional. Only the
URL is necessary. Without the URL, LibreNMS makes no call to Slack.

These attachment options are available:

- `author_name`

These global message options are available:

- `channel_name`: the Slack channel name of the alert, without the leading `#`
- `icon_emoji`: the emoji name in colon format for the author icon

[Slack docs](https://api.slack.com/docs/message-attachments)

The alert template can make use of
[Slack markdown](https://api.slack.com/reference/surfaces/formatting#basic-formatting).
The Slack markdown dialect marks a custom link with HTML angle
brackets, but LibreNMS removes them. For a custom link in an alert, use
the bracket and parenthesis markdown syntax. For example, a Slack link
is normally:

`<https://www.example.com|My Link>`

In your alert template, use this format:

`[My Link](https://www.example.com)`

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | <https://slack.com/url/somehook> |
| Channel | network-alerts |
| Author Name | LibreNMS Bot |
| Icon | `:scream:` |