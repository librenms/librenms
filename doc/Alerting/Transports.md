source: Alerting/Transports.md

# Transports

Transports are located within `LibreNMS/Alert/Transport/` and can be configured within the WebUI under Alerts -> Alert Transports.

Contacts will be gathered automatically and passed to the configured transports.
By default the Contacts will be only gathered when the alert triggers and will ignore future changes in contacts for the incident.
If you want contacts to be re-gathered before each dispatch, please set 'Updates to contact email addresses not honored' to Off in the WebUI.

The contacts will always include the `SysContact` defined in the Device's SNMP configuration and also every LibreNMS user that has at least `read`-permissions on the entity that is to be alerted.

At the moment LibreNMS only supports Port or Device permissions.

You can exclude the `SysContact` by toggling 'Issue alerts to sysContact'.

To include users that have `Global-Read`, `Administrator` or `Normal-User` permissions it is required to toggle the options:

  - Issue alerts to admins.
  - Issue alerts to read only users
  - Issue alerts to normal users.

## API

> You can configure these options within the WebUI now, please avoid setting these options within config.php

API transports definitions are a bit more complex than the E-Mail configuration.
The URL can have the same placeholders as defined in the [Template-Syntax](Templates#syntax).
If the `Api Method` is `get`, all placeholders will be URL-Encoded.
The API transport uses cURL to call the APIs, therefore you might need to install `php curl` to make it work.
__Note__: it is highly recommended to define your own [Templates](Templates) when you want to use the API transport. The default template might exceed URL-length for GET requests and therefore cause all sorts of errors.


## Boxcar

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling Boxcar support is super easy.
Copy your access token from the Boxcar app or from the Boxcar.io website and setup the transport.

## Canopsis

Canopsis is a hypervision tool. LibreNMS can send alerts to Canopsis which are then converted to canopsis events. 

You will need to fill this paramaters :

```php
Hostname = www.xxx.yyy.zzz
Port Number = 5672
User = admin
Password = my_password
Vhost = canopsis
```

For more information about canopsis and its events, take a look here :
 http://www.canopsis.org/
 http://www.canopsis.org/wp-content/themes/canopsis/doc/sakura/user-guide/event-spec.html

## Cisco Spark

[Using a proxy?](../Support/Configuration.md#proxy-support)

Cisco Spark. LibreNMS can send alerts to a Cisco Spark room. To make this possible you need to have a RoomID and a token. 

For more information about Cisco Spark RoomID and token, take a look here :

  - [Getting started](https://developer.ciscospark.com/getting-started.html)
  - [Rooms](https://developer.ciscospark.com/resource-rooms.html)

## Clickatell

[Using a proxy?](../Support/Configuration.md#proxy-support)

Clickatell provides a REST-API requiring an Authorization-Token and at least one Cellphone number.

[Clickatell Docs](https://www.clickatell.com/developers/api-documentation/rest-api-request-parameters/)

Here an example using 3 numbers, any amount of numbers is supported:

```php
+1234567890
+1234567891
+1234567892
```

## Discord

The Discord transport will POST the alert message to your Discord Incoming WebHook. Simple html tags are stripped from the message. 

The only required value is for url, without this no call to Discord will be made. The Options field supports the JSON/Form Params listed
in the Discord Docs below.

[Discord Docs](https://discordapp.com/developers/docs/resources/webhook#execute-webhook)

An example webhook url: 

```
https://discordapp.com/api/webhooks/4515489001665127664/82-sf4385ysuhfn34u2fhfsdePGLrg8K7cP9wl553Fg6OlZuuxJGaa1d54fe
```

## Elasticsearch

[Using a proxy?](../Support/Configuration.md#proxy-support)

You can have LibreNMS send alerts to an elasticsearch database. Each fault will be sent as a separate document.

The index pattern uses strftime() formatting.

As an example:

```php
Host = 127.0.0.1
Port = 9200
Index Patter = librenms-%Y.%m.%d
```

## Gitlab

LibreNMS will create issues for warning and critical level alerts however only title and description are set. 
Uses Personal access tokens to authenticate with Gitlab and will store the token in cleartext.

```php
Host = http://gitlab.host.tld
Project ID = 1
Personal Access Token = AbCdEf12345
```

## HipChat

[Using a proxy?](../Support/Configuration.md#proxy-support)

See the HipChat API Documentation for [rooms/message](https://www.hipchat.com/docs/api/method/rooms/message)
for details on acceptable values.

> You may notice that the link points at the "deprecated" v1 API.  This is
> because the v2 API is still in beta.

Below is an example of sending a message to a HipChat room.

```php
API URL = https://api.hipchat.com/v1/rooms/message?auth_token=109jawregoaihj
Room ID = 7654321
From Name = LibreNMS
Options = 
  color = red
  notify = 1
  message_format = text
```

At present the following options are supported: `color`, `notify` and `message_format`. 

> Note: The default message format for HipChat messages is HTML.  It is
> recommended that you specify the `text` message format to prevent unexpected
> results, such as HipChat attempting to interpret angled brackets (`<` and
> `>`).

## IRC

The IRC transports only works together with the LibreNMS IRC-Bot.
Configuration of the LibreNMS IRC-Bot is described [here](https://github.com/librenms/librenms/blob/master/doc/Extensions/IRC-Bot.md).

## JIRA

You can have LibreNMS create issues on a Jira instance for critical and warning alerts. The Jira transport only sets 
summary and description fields. Therefore your Jira project must not have any other mandatory field for the provided 
issuetype. The config fields that need to set are Jira URL, Jira username, Jira password, Project key, and issue type. 
Currently http authentication is used to access Jira and Jira username and password will be stored as cleartext in the 
LibreNMS database.

[Jira Issue Types](https://confluence.atlassian.com/adminjiracloud/issue-types-844500742.html)

```php
URL = https://myjira.mysite.com
Project Key = JIRAPROJECTKEY
Issue Type = Myissuetype
Jira Username = myjirauser
Jira Password = myjirapass
```

## Mail

For all but the default contact, we support setting multiple email addresses separated by a comma. So you can 
set the devices sysContact, override the sysContact or have your users emails set like:

`email@domain.com, alerting@domain.com`

The E-Mail transports uses the same email-configuration like the rest of LibreNMS.
As a small reminder, here is it's configuration directives including defaults:

## Microsoft Teams

[Using a proxy?](../Support/Configuration.md#proxy-support)

Microsoft Teams. LibreNMS can send alerts to Microsoft Teams Connector API which are then posted to a specific channel. 

```
$config['alert']['transports']['msteams']['url'] = 'https://outlook.office365.com/webhook/123456789';
```

## Nagios Compatible

> You can configure these options within the WebUI now, please avoid setting these options within config.php

The nagios transport will feed a FIFO at the defined location with the same format that nagios would.
This allows you to use other Alerting-Systems to work with LibreNMS, for example [Flapjack](http://flapjack.io).
```php
$config['alert']['transports']['nagios'] = "/path/to/my.fifo"; //Flapjack expects it to be at '/var/cache/nagios3/event_stream.fifo'
```

## OpsGenie

> You can configure these options within the WebUI now, please avoid setting these options within config.php

[Using a proxy?](../Support/Configuration.md#proxy-support)

Using OpsGenie LibreNMS integration, LibreNMS forwards alerts to OpsGenie with detailed information. OpsGenie acts as a dispatcher for LibreNMS alerts, determines the right people to notify based on on-call schedules– notifies via email, text messages (SMS), phone calls and iOS & Android push notifications, and escalates alerts until the alert is acknowledged or closed.

Create a [LibreNMS Integration](https://docs.opsgenie.com/docs/librenms-integration) from the integrations page once you signup. Then, copy the API key from OpsGenie to LibreNMS.

If you want to automatically ack and close alerts, leverage Marid integration. More detail with screenshots is available in [OpsGenie LibreNMS Integration page](https://docs.opsgenie.com/docs/librenms-integration).

## osTicket

[Using a proxy?](../Support/Configuration.md#proxy-support)

osTicket, open source ticket system. LibreNMS can send alerts to osTicket API which are then converted to osTicket tickets. To configure the transport, go to:

Global Settings -> Alerting Settings -> osTicket Transport.

This can also be done manually in config.php :

```php
$config['alert']['transports']['osticket']['url'] = 'http://osticket.example.com/api/http.php/tickets.json';
$config['alert']['transports']['osticket']['token'] = '123456789';
```

## PagerDuty

> You can configure these options within the WebUI now, please avoid setting these options within config.php

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling PagerDuty transports is almost as easy as enabling email-transports.

All you need is to create a Service with type Generic API on your PagerDuty dashboard.

Now copy your API-Key from the newly created Service and setup the transport like:

```php
$config['alert']['transports']['pagerduty'] = 'MYAPIKEYGOESHERE';
```

That's it!

__Note__: Currently ACK notifications are not transported to PagerDuty, This is going to be fixed within the next major version (version by date of writing: 2015.05)

## Philips Hue

Want to spice up your noc life? LibreNMS will flash all lights connected to your philips hue bridge whenever an alert is triggered. 

To setup, go to the you http://`your-bridge-ip`/debug/clip.html

- Update the "URL:" field to `/api`
- Paste this in the "Message Body" {"devicetype":"librenms"}
- Press the round button on your `philips Hue Bridge`
- Click on `POST`
- In the `Command Response` You should see output with your username. Copy this without the quotes


More Info: [Philips Hue Documentation](https://www.developers.meethue.com/documentation/getting-started)

```php
$config['alert']['transports']['hue']['bridge'] = 'http://bridge.example.com';
$config['alert']['transports']['hue']['user'] = 'af89jauaf98aj34r';
$config['alert']['transports']['hue']['duration'] = 'lselect';
```

## PlaySMS

[Using a proxy?](../Support/Configuration.md#proxy-support)

PlaySMS is an open source SMS-Gateway that can be used via their HTTP-API using a Username and WebService-Token.
Please consult PlaySMS's documentation regarding number formatting.
Here an example using 3 numbers, any amount of numbers is supported:

```php
$config['alert']['transports']['playsms']['url']   = 'https://localhost/index.php?app=ws';
$config['alert']['transports']['playsms']['user']  = 'user1';
$config['alert']['transports']['playsms']['token'] = 'MYFANCYACCESSTOKEN';
$config['alert']['transports']['playsms']['from']  = '+1234567892'; //Optional
$config['alert']['transports']['playsms']['to'][]  = '+1234567890';
$config['alert']['transports']['playsms']['to'][]  = '+1234567891';
```

## Pushbullet

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling Pushbullet is a piece of cake.
Get your Access Token from your Pushbullet's settings page and set it in your config like:

```php
$config['alert']['transports']['pushbullet'] = 'MYFANCYACCESSTOKEN';
```

## Pushover

[Using a proxy?](../Support/Configuration.md#proxy-support)

> You can configure these options within the WebUI now, please avoid setting these options within config.php

If you want to change the [notification sounds](https://pushover.net/api#sounds) then add it in config options as:

```php
sound_critical=falling
```

Enabling Pushover support is fairly easy, there are only two required parameters.

Firstly you need to create a new Application (called LibreNMS, for example) in your account on the Pushover website (https://pushover.net/apps)

Now copy your API Token/Key from the newly created Application and setup the transport in your config.php like:

```php
$config['alert']['transports']['pushover'][] = array(
                                                    "appkey" => 'APPLICATIONAPIKEYGOESHERE',
                                                    "userkey" => 'USERKEYGOESHERE',
                                                    );
```

To modify the Critical alert sound, add the 'sound_critical' parameter, example:

```php
$config['alert']['transports']['pushover'][] = array(
                                                    "appkey" => 'APPLICATIONAPIKEYGOESHERE',
                                                    "userkey" => 'USERKEYGOESHERE',
                                                    "sound_critical" => 'siren',
                                                    );
```

## Rocket.chat

[Using a proxy?](../Support/Configuration.md#proxy-support)

The Rocket.chat transport will POST the alert message to your Rocket.chat Incoming WebHook using the [attachments](https://rocket.chat/docs/developer-guides/rest-api/chat/postmessage) option, you are able to specify multiple webhooks along with the relevant options to go with it. Simple html tags are stripped from the message. All options are optional, the only required value is for url, without this then no call to Rocket.chat will be made. Below is an example of how to send alerts to two channels with different customised options:

```php
$config['alert']['transports']['rocket'][] = array('url' => "https://rocket.url/api/v1/chat.postMessage", 'channel' => '#Alerting');

$config['alert']['transports']['rocket'][] = array('url' => "https://rocket.url/api/v1/chat.postMessage", 'channel' => '@john', 'username' => 'LibreNMS', 'icon_emoji' => ':ghost:');
```

## Slack

> You can configure these options within the WebUI now, please avoid setting these options within config.php

[Using a proxy?](../Support/Configuration.md#proxy-support)

The Slack transport will POST the alert message to your Slack Incoming WebHook using the [attachments](https://api.slack.com/docs/message-attachments) option, you are able to specify multiple webhooks along with the relevant options to go with it. Simple html tags are stripped from the message. All options are optional, the only required value is for url, without this then no call to Slack will be made.

We currently support the following attachment options:

`author_name`

## SMSEagle

[Using a proxy?](../Support/Configuration.md#proxy-support)

SMSEagle is a hardware SMS Gateway that can be used via their HTTP-API using a Username and password
Please consult their documentation at [www.smseagle.eu](http://www.smseagle.eu)
Destination numbers are one per line, with no spaces. They can be in either local or international dialling format.

```php
$config['alert']['transports']['smseagle']['url']   = 'ip.add.re.ss';
$config['alert']['transports']['smseagle']['user']  = 'smseagle_user';
$config['alert']['transports']['smseagle']['token'] = 'smseagle_user_password';
$config['alert']['transports']['smseagle']['to'][]  = '+3534567890';
$config['alert']['transports']['smseagle']['to'][]  = '0834567891';
```

## Syslog

You can have LibreNMS emit alerts as syslogs complying with RFC 3164.
More information on RFC 3164 can be found here: https://tools.ietf.org/html/rfc3164
Example output: `<26> Mar 22 00:59:03 librenms.host.net librenms[233]: [Critical] network.device.net: Port Down - port_id => 98939; ifDescr => xe-1/1/0;`
Each fault will be sent as a separate syslog.

```php
$config['alert']['transports']['syslog']['syslog_host']   = '127.0.0.1';
$config['alert']['transports']['syslog']['syslog_port']  = 514;
$config['alert']['transports']['syslog']['syslog_facility'] = 3;
```

## Telegram

[Using a proxy?](../Support/Configuration.md#proxy-support)

> Thank you to [snis](https://github.com/snis) for these instructions.

1. First you must create a telegram account and add BotFather to you list. To do this click on the following url: https://telegram.me/botfather

2. Generate a new bot with the command "/newbot" BotFather is then asking for a username and a normal name. After that your bot is created and you get a HTTP token. (for more options for your bot type "/help")

3. Add your bot to telegram with the following url: `http://telegram.me/<botname>` and send some text to the bot.

4. The BotFather should have responded with a token, copy your token code and go to the following page in chrome: `https://api.telegram.org/bot<tokencode>/getUpdates`
(this could take a while so continue to refresh until you see something similar to below)

5. You see a json code with the message you sent to the bot. Copy the Chat id. In this example that is “-9787468”
   `"message":{"message_id":7,"from":"id":656556,"first_name":"Joo","last_name":"Doo","username":"JohnDoo"},"chat":{"id":-9787468,"title":"Telegram Group"},"date":1435216924,"text":"Hi"}}]}`
   
6. Now create a new "Telegram transport" in LibreNMS (Global Settings -> Alerting Settings -> Telegram transport).
Click on 'Add Telegram config' and put your chat id and token into the relevant box.


## VictorOps

[Using a proxy?](../Support/Configuration.md#proxy-support)

VictorOps provide a webHook url to make integration extremely simple. To get the URL required login to your VictorOps account and go to:

Settings -> Integrations -> REST Endpoint -> Enable Integration.

The URL provided will have $routing_key at the end, you need to change this to something that is unique to the system sending the alerts such as librenms. I.e:

`https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms`

```php
$config['alert']['transports']['victorops']['url'] = 'https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms';
```
