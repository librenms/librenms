## Telegram

> Thank you to [snis](https://github.com/snis) for these instructions.

1. First you must create a telegram account and add BotFather to you
   list. To do this click on the following url:
   [https://telegram.me/botfather](https://telegram.me/botfather)

1. Generate a new bot with the command "/newbot" BotFather is then
   asking for a username and a normal name. After that your bot is
   created and you get a HTTP token. (for more options for your bot
   type "/help")

1. Add your bot to telegram with the following url:
   `http://telegram.me/<botname>` to use app or
   `https://web.telegram.org/<botname>` to use in web, and send some
   text to the bot.

1. The BotFather should have responded with a token, copy your token
   code and go to the following page in chrome:
   `https://api.telegram.org/bot<tokencode>/getUpdates` (this could
   take a while so continue to refresh until you see something similar
   to below)

1. You see a json code with the message you sent to the bot. Copy the
   Chat id. In this example that is “-9787468” within this example:
   `"message":{"message_id":7,"from":"id":656556,"first_name":"Joo","last_name":"Doo","username":"JohnDoo"},"chat":{"id":-9787468,"title":"Telegram
   Group"},"date":1435216924,"text":"Hi"}}]}`.

1. Now create a new "Telegram transport" in LibreNMS (Global Settings
   -> Alerting Settings -> Telegram transport). Click on 'Add Telegram
   config' and put your chat id and token into the relevant box.

1. If want to use a group to receive alerts, you need to pick the Chat
   ID of the group chat, and not of the Bot itself.

[Telegram Docs](https://core.telegram.org/api)

**Configuration Example:**

| Config | Example |
| ------ | ------- |
| Chat ID | 34243432 |
| Token | 3ed32wwf235234 |
| Format | HTML or MARKDOWN |
| Send PNG Graph As | photo or file |

**Template Example:**

This will send a set of images (photos or files) followed by the text message, every [signedGraphTag](../Templates/#signedgraphtag) helper will be removed from the message content. 

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
