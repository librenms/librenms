## Telegram

> Thank you to [snis](https://github.com/snis) for these instructions.

1. Create a Telegram account. Then add BotFather to your list at
   [https://telegram.me/botfather](https://telegram.me/botfather).

1. Generate a new bot with the command "/newbot". BotFather asks for a
   username and a normal name. It then creates your bot and gives you an
   HTTP token. For more bot options, use the command "/help".

1. Add your bot to Telegram. In the app, use
   `http://telegram.me/<botname>`. In a browser, use
   `https://web.telegram.org/<botname>`. Then send some text to the bot.

1. Copy the token from BotFather. Then open
   `https://api.telegram.org/bot<tokencode>/getUpdates` in a browser.
   This step can take some time. Refresh the page until output like the
   text below appears.

1. The page shows JSON with your message to the bot. Copy the chat id.
   In this example, the chat id is `-9787468`:
   `"message":{"message_id":7,"from":"id":656556,"first_name":"Joo","last_name":"Doo","username":"JohnDoo"},"chat":{"id":-9787468,"title":"Telegram
   Group"},"date":1435216924,"text":"Hi"}}]}`.

1. Create a new Telegram transport in LibreNMS at Global Settings ->
   Alerting Settings -> Telegram transport. Click 'Add Telegram config'.
   Then enter your chat id and your token.

1. To send the alerts to a group, use the chat id of the group chat,
   not the chat id of the bot.

[Telegram Docs](https://core.telegram.org/api)

**Configuration Example:**

| Config | Example |
| ------ | ------- |
| Chat ID | 34243432 |
| Token | 3ed32wwf235234 |
| Format | HTML or MARKDOWN |
| Send PNG Graph As | photo or file |

**Template Example:**

This template sends a set of images, that is photos or files, and then
the text message. LibreNMS removes each
[signedGraphTag](../Templates.md/#signedgraphtag) helper from the
message content.

```php
{{ $alert->title }}
Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif
@foreach ($alert->faults as $key => $value)
Physical Interface: {{ $value['ifDescr'] }}
Interface Description: {{ $value['ifAlias'] }}
Interface Speed: {{ ($value['ifSpeed']/1000000000) }} Gbs
Inbound Utilization: {{ (($value['ifInOctets_rate']*8)/$value['ifSpeed'])*100 }}
Outbound Utilization: {{ (($value['ifOutOctets_rate']*8)/$value['ifSpeed'])*100 }}
@signedGraphTag([
    'id' => $value['port_id'],
    'type' => 'port_bits',
    'from' => time() - 43200,
    'to' => time(),
    'width' => 700, 
    'height' => 250,
    'title' => 'yes',
])
@endforeach

```
