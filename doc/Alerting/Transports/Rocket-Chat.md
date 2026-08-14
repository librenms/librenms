## Rocket.chat

The Rocket.chat transport sends the alert message to your Rocket.chat
incoming webhook with a POST request. It uses the attachments option.
LibreNMS removes the simple HTML tags from the message. All options are
optional. Only the URL is necessary. Without the URL, LibreNMS makes no
call to Rocket.chat.

[Rocket.chat Docs](https://rocket.chat/docs/developer-guides/rest-api/chat/postmessage)

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | https://rocket.url/api/v1/chat.postMessage |
| Rocket.chat Options | channel=#Alerting <br/> username=myname <br/> icon_url=http://someurl/image.gif <br/> icon_emoji=:smirk: |