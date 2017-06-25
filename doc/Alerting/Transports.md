source: Alerting/Transports.md

# <a name="transports">Transports</a>

Transports are located within `includes/alerts/transports.*.php` and defined as well as configured via ~~`$config['alert']['transports']['Example'] = 'Some Options'`~~.

Contacts will be gathered automatically and passed to the configured transports.
By default the Contacts will be only gathered when the alert triggers and will ignore future changes in contacts for the incident. If you want contacts to be re-gathered before each dispatch, please set ~~`$config['alert']['fixed-contacts'] = false;`~~ in your config.php.

The contacts will always include the `SysContact` defined in the Device's SNMP configuration and also every LibreNMS-User that has at least `read`-permissions on the entity that is to be alerted.

At the moment LibreNMS only supports Port or Device permissions.

You can exclude the `SysContact` by setting:

```php
$config['alert']['syscontact'] = false;
```

To include users that have `Global-Read` or `Administrator` permissions it is required to add these additions to the `config.php` respectively:

```php
$config['alert']['globals'] = true; //Include Global-Read into alert-contacts
$config['alert']['admins']  = true; //Include Administrators into alert-contacts
```

## <a name="transports-email">E-Mail</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

For all but the default contact, we support setting multiple email addresses separated by a comma. So you can 
set the devices sysContact, override the sysContact or have your users emails set like:

`email@domain.com, alerting@domain.com`

E-Mail transport is enabled with adding the following to your `config.php`:
~
```php
$config['alert']['transports']['mail'] = true;
```
~~

The E-Mail transports uses the same email-configuration like the rest of LibreNMS.
As a small reminder, here is it's configuration directives including defaults:
~~
```php
$config['email_backend']                   = 'mail';               // Mail backend. Allowed: "mail" (PHP's built-in), "sendmail", "smtp".
$config['email_from']                      = NULL;                 // Mail from. Default: "ProjectName" <projectid@`hostname`>
$config['email_user']                      = $config['project_id'];
$config['email_sendmail_path']             = '/usr/sbin/sendmail'; // The location of the sendmail program.
$config['email_html']                      = FALSE;                // Whether to send HTML email as opposed to plaintext
$config['email_smtp_host']                 = 'localhost';          // Outgoing SMTP server name.
$config['email_smtp_port']                 = 25;                   // The port to connect.
$config['email_smtp_timeout']              = 10;                   // SMTP connection timeout in seconds.
$config['email_smtp_secure']               = NULL;                 // Enable encryption. Use 'tls' or 'ssl'
$config['email_smtp_auth']                 = FALSE;                // Whether or not to use SMTP authentication.
$config['email_smtp_username']             = NULL;                 // SMTP username.
$config['email_smtp_password']             = NULL;                 // Password for SMTP authentication.

$config['alert']['default_only']           = false;                //Only issue to default_mail
$config['alert']['default_mail']           = '';                   //Default email
```
~~

## <a name="transports-api">API</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

API transports definitions are a bit more complex than the E-Mail configuration.
The basis for configuration is ~~`$config['alert']['transports']['api'][METHOD]`~~ where `METHOD` can be `get`,`post` or `put`.
This basis has to contain an array with URLs of each API to call.
The URL can have the same placeholders as defined in the [Template-Syntax](#templates-syntax).
If the `METHOD` is `get`, all placeholders will be URL-Encoded.
The API transport uses cURL to call the APIs, therefore you might need to install `php5-curl` or similar in order to make it work.
__Note__: it is highly recommended to define own [Templates](#templates) when you want to use the API transport. The default template might exceed URL-length for GET requests and therefore cause all sorts of errors.

Example:
~~
```php
$config['alert']['transports']['api']['get'][] = "https://api.thirdparti.es/issue?apikey=abcdefg&subject=%title";
```
~~

## <a name="transports-nagios">Nagios Compatible</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

The nagios transport will feed a FIFO at the defined location with the same format that nagios would.
This allows you to use other Alerting-Systems to work with LibreNMS, for example [Flapjack](http://flapjack.io).
~~
```php
$config['alert']['transports']['nagios'] = "/path/to/my.fifo"; //Flapjack expects it to be at '/var/cache/nagios3/event_stream.fifo'
```
~~

## <a name="transports-irc">IRC</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

The IRC transports only works together with the LibreNMS IRC-Bot.
Configuration of the LibreNMS IRC-Bot is described [here](https://github.com/librenms/librenms/blob/master/doc/Extensions/IRC-Bot.md).
~~
```php
$config['alert']['transports']['irc'] = true;
```
~~

## <a name="transports-slack">Slack</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

[Using a proxy?](../Support/Configuration.md#proxy-support)

The Slack transport will POST the alert message to your Slack Incoming WebHook using the [attachments](https://api.slack.com/docs/message-attachments) option, you are able to specify multiple webhooks along with the relevant options to go with it. Simple html tags are stripped from the message. All options are optional, the only required value is for url, without this then no call to Slack will be made. Below is an example of how to send alerts to two channels with different customised options: 

~~
```php
$config['alert']['transports']['slack'][] = array('url' => "https://hooks.slack.com/services/A12B34CDE/F56GH78JK/L901LmNopqrSTUVw2w3XYZAB4C", 'channel' => '#Alerting');

$config['alert']['transports']['slack'][] = array('url' => "https://hooks.slack.com/services/A12B34CDE/F56GH78JK/L901LmNopqrSTUVw2w3XYZAB4C", 'channel' => '@john', 'username' => 'LibreNMS', 'icon_emoji' => ':ghost:');

```
~~

## <a name="transports-slack">Rocket.chat</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

The Rocket.chat transport will POST the alert message to your Rocket.chat Incoming WebHook using the [attachments](https://rocket.chat/docs/developer-guides/rest-api/chat/postmessage) option, you are able to specify multiple webhooks along with the relevant options to go with it. Simple html tags are stripped from the message. All options are optional, the only required value is for url, without this then no call to Rocket.chat will be made. Below is an example of how to send alerts to two channels with different customised options:

```php
$config['alert']['transports']['rocket'][] = array('url' => "https://rocket.url/api/v1/chat.postMessage", 'channel' => '#Alerting');

$config['alert']['transports']['rocket'][] = array('url' => "https://rocket.url/api/v1/chat.postMessage", 'channel' => '@john', 'username' => 'LibreNMS', 'icon_emoji' => ':ghost:');

```

## <a name="transports-hipchat">HipChat</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

[Using a proxy?](../Support/Configuration.md#proxy-support)

The HipChat transport requires the following:

__room_id__ = HipChat Room ID

__url__ = HipChat API URL+API Key

__from__ = The name that will be displayed

The HipChat transport makes the following optional:

__color__ = Any of HipChat's supported message colors

__message_format__ = Any of HipChat's supported message formats

__notify__ = 0 or 1

See the HipChat API Documentation for
[rooms/message](https://www.hipchat.com/docs/api/method/rooms/message)
for details on acceptable values.

> You may notice that the link points at the "deprecated" v1 API.  This is
> because the v2 API is still in beta.

Below are two examples of sending messages to a HipChat room.

~~
```php
$config['alert']['transports']['hipchat'][] = array("url" => "https://api.hipchat.com/v1/rooms/message?auth_token=9109jawregoaih",
                                                    "room_id" => "1234567",
                                                    "from" => "LibreNMS");

$config['alert']['transports']['hipchat'][] = array("url" => "https://api.hipchat.com/v1/rooms/message?auth_token=109jawregoaihj",
                                                    "room_id" => "7654321",
                                                    "from" => "LibreNMS",
                                                    "color" => "red",
                                                    "notify" => 1,
                                                    "message_format" => "text");
```
~~

> Note: The default message format for HipChat messages is HTML.  It is
> recommended that you specify the `text` message format to prevent unexpected
> results, such as HipChat attempting to interpret angled brackets (`<` and
> `>`).

## <a name="transports-pagerduty">PagerDuty</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling PagerDuty transports is almost as easy as enabling email-transports.

All you need is to create a Service with type Generic API on your PagerDuty dashboard.

Now copy your API-Key from the newly created Service and setup the transport like:

~~
```php
$config['alert']['transports']['pagerduty'] = 'MYAPIKEYGOESHERE';
```
~~

That's it!

__Note__: Currently ACK notifications are not transported to PagerDuty, This is going to be fixed within the next major version (version by date of writing: 2015.05)

## <a name="transports-pushover">Pushover</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling Pushover support is fairly easy, there are only two required parameters.

Firstly you need to create a new Application (called LibreNMS, for example) in your account on the Pushover website (https://pushover.net/apps)

Now copy your API Token/Key from the newly created Application and setup the transport in your config.php like:

~~
```php
$config['alert']['transports']['pushover'][] = array(
                                                    "appkey" => 'APPLICATIONAPIKEYGOESHERE',
                                                    "userkey" => 'USERKEYGOESHERE',
                                                    );
```
~~

To modify the Critical alert sound, add the 'sound_critical' parameter, example:

~~
```php
$config['alert']['transports']['pushover'][] = array(
                                                    "appkey" => 'APPLICATIONAPIKEYGOESHERE',
                                                    "userkey" => 'USERKEYGOESHERE',
                                                    "sound_critical" => 'siren',
                                                    );
```
~~

## <a name="transports-boxcar">Boxcar</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling Boxcar support is super easy.
Copy your access token from the Boxcar app or from the Boxcar.io website and setup the transport in your config.php like:

~~
```php
$config['alert']['transports']['boxcar'][] = array(
                                                    "access_token" => 'ACCESSTOKENGOESHERE',
                                                    );
```
~~

To modify the Critical alert sound, add the 'sound_critical' parameter, example:

~~
```php
$config['alert']['transports']['boxcar'][] = array(
                                                    "access_token" => 'ACCESSTOKENGOESHERE',
                                                    "sound_critical" => 'detonator-charge',
                                                    );
```
~~

## <a name="transports-telegram">Telegram</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

> Thank you to [snis](https://github.com/snis) for these instructions.

1. First you must create a telegram account and add BotFather to you list. To do this click on the following url: https://telegram.me/botfather

2. Generate a new bot with the command "/newbot" BotFather is then asking for a username and a normal name. After that your bot is created and you get a HTTP token. (for more options for your bot type "/help")

3. Add your bot to telegram with the following url: `http://telegram.me/<botname>` and send some text to the bot.

4. Now copy your token code and go to the following page in chrome: `https://api.telegram.org/bot<tokencode>/getUpdates`

5. You see a json code with the message you sent to the bot. Copy the Chat id. In this example that is “-9787468”
   `"message":{"message_id":7,"from":"id":656556,"first_name":"Joo","last_name":"Doo","username":"JohnDoo"},"chat":{"id":-9787468,"title":"Telegram Group"},"date":1435216924,"text":"Hi"}}]}`
   
6. Now create a new "Telegram transport" in LibreNMS (Global Settings -> Alerting Settings -> Telegram transport).
Click on 'Add Telegram config' and put your chat id and token into the relevant box.

## <a name="transports-pushbullet">Pushbullet</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

Enabling Pushbullet is a piece of cake.
Get your Access Token from your Pushbullet's settings page and set it in your config like:

~~
```php
$config['alert']['transports']['pushbullet'] = 'MYFANCYACCESSTOKEN';
```
~~

## <a name="transports-clickatell">Clickatell</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

Clickatell provides a REST-API requiring an Authorization-Token and at least one Cellphone number.
Please consult Clickatell's documentation regarding number formatting.
Here an example using 3 numbers, any amount of numbers is supported:

~~
```php
$config['alert']['transports']['clickatell']['token'] = 'MYFANCYACCESSTOKEN';
$config['alert']['transports']['clickatell']['to'][]  = '+1234567890';
$config['alert']['transports']['clickatell']['to'][]  = '+1234567891';
$config['alert']['transports']['clickatell']['to'][]  = '+1234567892';
```
~~

## <a name="transports-playsms">PlaySMS</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

PlaySMS is an open source SMS-Gateway that can be used via their HTTP-API using a Username and WebService-Token.
Please consult PlaySMS's documentation regarding number formatting.
Here an example using 3 numbers, any amount of numbers is supported:

~~
```php
$config['alert']['transports']['playsms']['url']   = 'https://localhost/index.php?app=ws';
$config['alert']['transports']['playsms']['user']  = 'user1';
$config['alert']['transports']['playsms']['token'] = 'MYFANCYACCESSTOKEN';
$config['alert']['transports']['playsms']['from']  = '+1234567892'; //Optional
$config['alert']['transports']['playsms']['to'][]  = '+1234567890';
$config['alert']['transports']['playsms']['to'][]  = '+1234567891';
```
~~

## <a name="transports-victorops">VictorOps</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

VictorOps provide a webHook url to make integration extremely simple. To get the URL required login to your VictorOps account and go to:

Settings -> Integrations -> REST Endpoint -> Enable Integration.

The URL provided will have $routing_key at the end, you need to change this to something that is unique to the system sending the alerts such as librenms. I.e:

`https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms`

~~
```php
$config['alert']['transports']['victorops']['url'] = 'https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms';
```
~~

## <a name="transports-canopsis">Canopsis</a>

Canopsis is a hypervision tool. LibreNMS can send alerts to Canopsis which are then converted to canopsis events. To configure the transport, go to:

Global Settings -> Alerting Settings -> Canopsis Transport.

You will need to fill this paramaters :

~~
```php
$config['alert']['transports']['canopsis']['host'] = 'www.xxx.yyy.zzz';
$config['alert']['transports']['canopsis']['port'] = '5672';
$config['alert']['transports']['canopsis']['user'] = 'admin';
$config['alert']['transports']['canopsis']['passwd'] = 'my_password';
$config['alert']['transports']['canopsis']['vhost'] = 'canopsis';
```
~~

For more information about canopsis and its events, take a look here :
 http://www.canopsis.org/
 http://www.canopsis.org/wp-content/themes/canopsis/doc/sakura/user-guide/event-spec.html

## <a name="transports-osticket">osTicket</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

osTicket, open source ticket system. LibreNMS can send alerts to osTicket API which are then converted to osTicket tickets. To configure the transport, go to:

Global Settings -> Alerting Settings -> osTicket Transport.

This can also be done manually in config.php :

~~
```php
$config['alert']['transports']['osticket']['url'] = 'http://osticket.example.com/api/http.php/tickets.json';
$config['alert']['transports']['osticket']['token'] = '123456789';
```
~~

## <a name="transports-msteams">Microsoft Teams</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

Microsoft Teams. LibreNMS can send alerts to Microsoft Teams Connector API which are then posted to a specific channel. To configure the transport, go to:

Global Settings -> Alerting Settings -> Microsoft Teams Transport.

This can also be done manually in config.php :

~
```php
$config['alert']['transports']['msteams']['url'] = 'https://outlook.office365.com/webhook/123456789';
```
~

## <a name="transports-ciscospark">Cisco Spark</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)


Cisco Spark. LibreNMS can send alerts to a Cisco Spark room. To make this possible you need to have a RoomID and a token. 

For more information about Cisco Spark RoomID and token, take a look here :
 https://developer.ciscospark.com/getting-started.html
 https://developer.ciscospark.com/resource-rooms.html

To configure the transport, go to:

Global Settings -> Alerting Settings -> Cisco Spark transport.

This can also be done manually in config.php :

~
```php
$config['alert']['transports']['ciscospark']['token'] = '1234567890QWERTYUIOP';
$config['alert']['transports']['ciscospark']['roomid'] = '1234567890QWERTYUIOP';
```
~

## <a name="transports-smseagle">SMSEagle</a>

[Using a proxy?](../Support/Configuration.md#proxy-support)

SMSEagle is a hardware SMS Gateway that can be used via their HTTP-API using a Username and password
Please consult their documentation at [www.smseagle.eu](http://www.smseagle.eu)
Destination numbers are one per line, with no spaces. They can be in either local or international dialling format.

~
```php
$config['alert']['transports']['smseagle']['url']   = 'ip.add.re.ss';
$config['alert']['transports']['smseagle']['user']  = 'smseagle_user';
$config['alert']['transports']['smseagle']['token'] = 'smseagle_user_password';
$config['alert']['transports']['smseagle']['to'][]  = '+3534567890';
$config['alert']['transports']['smseagle']['to'][]  = '0834567891';
```
~

## <a name="transports-syslog">Syslog</a>

You can have LibreNMS emit alerts as syslogs complying with RFC 3164.
More information on RFC 3164 can be found here: https://tools.ietf.org/html/rfc3164
Example output: `<26> Mar 22 00:59:03 librenms.host.net librenms[233]: [Critical] network.device.net: Port Down - port_id => 98939; ifDescr => xe-1/1/0;`
Each fault will be sent as a separate syslog.

~
```php
$config['alert']['transports']['syslog']['syslog_host']   = '127.0.0.1';
$config['alert']['transports']['syslog']['syslog_port']  = 514;
$config['alert']['transports']['syslog']['syslog_facility'] = 3;
```
~

## <a name="transports-elasticsearch">Elasticsearch</a>

You can have LibreNMS emit alerts to an elasticsearch database. Each fault will be sent as a separate document.
The index pattern uses strftime() formatting.
The proxy setting uses the proxy set in config.php if true and does not if false; this allows you to use local servers.

~
```php
$config['alert']['transports']['elasticsearch']['es_host']   = '127.0.0.1';
$config['alert']['transports']['elasticsearch']['es_port']  = 9200;
$config['alert']['transports']['elasticsearch']['es_index']  = 'librenms-%Y.%m.%d';
$config['alert']['transports']['elasticsearch']['es_proxy'] = false;
```
~
