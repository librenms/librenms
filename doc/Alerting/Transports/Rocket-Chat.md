## Rocket.chat

The Rocket.chat transport will POST the alert message to your
Rocket.chat Incoming WebHook using the attachments option. Simple html
tags are stripped from the message. All options are optional, the only
required value is for url, without this then no call to Rocket.chat will be made.

[Rocket.chat Docs](https://rocket.chat/docs/developer-guides/rest-api/chat/postmessage)

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | https://rocket.url/api/v1/chat.postMessage |
| Rocket.chat Options | channel=#Alerting <br/> username=myname <br/> icon_url=http://someurl/image.gif <br/> icon_emoji=:smirk: |