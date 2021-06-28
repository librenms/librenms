source: Alerting/Transports.md
path: blob/master/doc/

# Transports

Transports are located within `LibreNMS/Alert/Transport/` and can be
configured within the WebUI under Alerts -> Alert Transports.

Contacts will be gathered automatically and passed to the configured transports.
By default the Contacts will be only gathered when the alert triggers
and will ignore future changes in contacts for the incident.
If you want contacts to be re-gathered before each dispatch, please
set 'Updates to contact email addresses not honored' to Off in the WebUI.

The contacts will always include the `SysContact` defined in the
Device's SNMP configuration and also every LibreNMS user that has at
least `read`-permissions on the entity that is to be alerted.

At the moment LibreNMS only supports Port or Device permissions.

You can exclude the `SysContact` by toggling 'Issue alerts to sysContact'.

To include users that have `Global-Read`, `Administrator` or
`Normal-User` permissions it is required to toggle the options:

- Issue alerts to admins.
- Issue alerts to read only users
- Issue alerts to normal users.

## Using a Proxy

[Proxy Configuration](../Support/Configuration.md#proxy-support)

## Using a AMQP based Transport

You need to install an additional php module : `bcmath`

## Alerta

The [alerta](https://alerta.io) monitoring system is a tool used to consolidate and de-duplicate alerts from multiple sources for quick ‘at-a-glance’ visualisation. With just one system you can monitor alerts from many other monitoring tools on a single screen.

**Example:**

| Config | Example |
| ------ | ------- |
| API Endpoint   | http://alerta.example.com/api/alert |
| Environment | Production |
| Apy key | api key with write permission |
| Alert state | critical |
| Recover state | cleared |

## Alertmanager

Alertmanager is an alert handling software, initially developed for
alert processing sent by Prometheus.

It has built-in functionality for deduplicating, grouping and routing
alerts based on configurable criteria.

LibreNMS uses alert grouping by alert rule, which can produce an array
of alerts of similar content for an array of hosts, whereas
Alertmanager can group them by alert meta, ideally producing one
single notice in case an issue occurs.

It is possible to configure as many label values as required in
Alertmanager Options section. Every label and its value should be
entered as a new line.

Multiple Alertmanager URLs (comma separated) are supported. Each
URL will be tried and the search will stop at the first success.

[Alertmanager Docs](https://prometheus.io/docs/alerting/alertmanager/)

**Example:**

| Config | Example |
| ------ | ------- |
| Alertmanager URL(s)   | http://alertmanager1.example.com,http://alertmanager2.example.com |
| Alertmanager Options: | source=librenms <br/> customlabel=value |

## API

The API transport allows to reach any service provider using POST, PUT or GET URLs
(Like SMS provider, etc). It can be used in multiple ways:

- The same text built from the Alert template is available in the variable
``` $msg ```, which can then be sent as an option to the API. Be carefull that
HTTP GET requests are usually limited in length.
- The API-Option fields can be directly built from the variables defined in
[Template-Syntax](Templates.md#syntax) but without the 'alert->' prefix.
For instance, ``` $alert->uptime ``` is available as ``` $uptime ``` in the
API transport
- The API-Headers allows you to add the headers that the api endpoint requires.
- The API-body allow sending data in the format required by the ApI endpoint.

A few variables commonly used :

| Variable            | Description |
| ------------------  | ----------- |
| {{ $hostname }}     | Hostname |
| {{ $sysName }}      | SysName |
| {{ $sysDescr }}     | SysDescr |
| {{ $os }}           | OS of device (librenms defined) |
| {{ $type }}         | Type of device (librenms defined) |
| {{ $ip }}           | IP Address |
| {{ $hardware }}     | Hardware |
| {{ $version }}      | Version |
| {{ $uptime }}       | Uptime in seconds |
| {{ $uptime_short }} | Uptime in human-readable format |
| {{ $timestamp }}    | Timestamp of alert |
| {{ $description }}  | Description of device |
| {{ $title }}        | Title (as built from the Alert Template) |
| {{ $msg }}          | Body text (as built from the Alert Template) |

**Example:**

The example below will use the API named sms-api of my.example.com and send
the title of the alert to the provided number using the provided service key.
Refer to your service documentation to configure it properly.

| Config | Example |
| ------ | ------- |
| API Method    | GET |
| API URL       | <http://my.example.com/sms-api>
| API Options   | rcpt=0123456789 <br/> key=0987654321abcdef <br/> msg=(LNMS) {{ $title }} |
| API Username  | myUsername |
| API Password  | myPassword |

The example below will use the API named wall-display of my.example.com and send
the title and text of the alert to a screen in the Network Operation Center.

| Config | Example |
| ------ | ------- |
| API Method    | POST |
| API URL       | <http://my.example.com/wall-display>
| API Options   | title={{ $title }} <br/> msg={{ $msg }}|

The example below will use the API named component of my.example.com with id 1, body as json status value and headers send token authentication and content type required.

| Config | Example |
| ------ | ------- |
| API Method    | PUT |
| API URL       | http://my.example.com/comonent/1
| API Headers   | X-Token=HASH
|               | Content-Type=application/json
| API Body      | { "status": 2 }


## aspSMS
aspSMS is a SMS provider that can be configured by using the generic API Transport.
You need a token you can find on your personnal space.

[aspSMS docs](https://www.aspsms.com/en/documentation/)

**Example:**

| Config | Example |
| ------ | ------- |
| Transport type | Api |
| API Method | POST |
| API URL | https://soap.aspsms.com/aspsmsx.asmx/SimpleTextSMS |
| Options | UserKey=USERKEY<br />Password=APIPASSWORD<br />Recipient=RECIPIENT<br />Originator=ORIGINATOR<br />MessageText={{ $msg }} |

## Boxcar

Copy your access token from the Boxcar app or from the Boxcar.io
website and setup the transport.

[Boxcar Docs](http://developer.boxcar.io/api/publisher/)

**Example:**

| Config | Example |
| ------ | ------- |
| Access Token | i23f23mr23rwerw |

## Canopsis

Canopsis is a hypervision tool. LibreNMS can send alerts to Canopsis
which are then converted to canopsis events.

[Canopsis Docs](https://doc.canopsis.net/guide-developpement/struct-event/)

**Example:**

| Config | Example |
| ------ | ------- |
| Hostname | www.xxx.yyy.zzz |
| Port Number | 5672 |
| User | admin |
| Password | my_password |
| Vhost | canopsis |

## Cisco Spark (aka Webex Teams)

Cisco Spark (now known as Webex Teams). LibreNMS can send alerts to a Cisco
Spark room. To make this possible you need to have a RoomID and a token.
You can also choose to send alerts using Markdown syntax.  Enabling this
option provides for more richly formatted alerts, but be sure to adjust your
alert template to account for the Markdown syntax.

For more information about Cisco Spark RoomID and token, take a look here :

- [Getting started](https://developer.ciscospark.com/getting-started.html)
- [Rooms](https://developer.ciscospark.com/resource-rooms.html)

**Example:**

| Config | Example |
| ------ | ------- |
| API Token | ASd23r23edewda |
| RoomID | 34243243251 |
| Use Markdown? | x |

## Clickatell

Clickatell provides a REST-API requiring an Authorization-Token and at
least one Cellphone number.

[Clickatell Docs](https://www.clickatell.com/developers/api-documentation/rest-api-request-parameters/)

Here an example using 3 numbers, any amount of numbers is supported:

**Example:**

| Config | Example |
| ------ | ------- |
| Token | dsaWd3rewdwea |
| Mobile Numbers | +1234567890,+1234567891,+1234567892 |

## Discord

The Discord transport will POST the alert message to your Discord
Incoming WebHook. Simple html tags are stripped from  the message.

The only required value is for url, without this no call to Discord
will be made. The Options field supports the JSON/Form Params listed
in the Discord Docs below.

[Discord Docs](https://discordapp.com/developers/docs/resources/webhook#execute-webhook)

**Example:**

| Config | Example |
| ------ | ------- |
| Discord URL | <https://discordapp.com/api/webhooks/4515489001665127664/82-sf4385ysuhfn34u2fhfsdePGLrg8K7cP9wl553Fg6OlZuuxJGaa1d54fe> |
| Options | username=myname |

## Elasticsearch

You can have LibreNMS send alerts to an elasticsearch database. Each
fault will be sent as a separate document.

The index pattern uses strftime() formatting.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| Port | 9200 |
| Index Patter | librenms-%Y.%m.%d |

## GitLab

LibreNMS will create issues for warning and critical level alerts
however only title and description are set. Uses Personal access
tokens to authenticate with GitLab and will store the token in cleartext.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | <http://gitlab.host.tld> |
| Project ID | 1 |
| Personal Access Token | AbCdEf12345 |

## HipChat

See the HipChat API Documentation for [rooms/message](https://www.hipchat.com/docs/api/method/rooms/message)
for details on acceptable values.

> You may notice that the link points at the "deprecated" v1 API.  This is
> because the v2 API is still in beta.

**Example:**

| Config | Example |
| ------ | ------- |
| API URL | <https://api.hipchat.com/v1/rooms/message?auth_token=109jawregoaihj> |
| Room ID | 7654321 |
| From Name | LibreNMS |
| Options | color = red <br/> notify = 1 <br/> message_format = text |

At present the following options are supported: `color`, `notify` and `message_format`.

> Note: The default message format for HipChat messages is HTML.  It is
> recommended that you specify the `text` message format to prevent unexpected
> results, such as HipChat attempting to interpret angled brackets (`<` and
> `>`).

## IRC

The IRC transports only works together with the LibreNMS IRC-Bot.
Configuration of the LibreNMS IRC-Bot is described [here](https://github.com/librenms/librenms/blob/master/doc/Extensions/IRC-Bot.md).

**Example:**

| Config | Example |
| ------ | ------- |
| IRC | enabled |

## JIRA

You can have LibreNMS create issues on a Jira instance for critical
and warning alerts. The Jira transport only sets  summary and
description fields. Therefore your Jira project must not have any
other mandatory field for the provided issuetype. The config fields
that need to set are Jira URL, Jira username, Jira password, Project
key, and issue type.  Currently http authentication is used to access
Jira and Jira username and password will be stored as cleartext in the
LibreNMS database.

[Jira Issue Types](https://confluence.atlassian.com/adminjiracloud/issue-types-844500742.html)

**Example:**

| Config | Example |
| ------ | ------- |
| URL | <https://myjira.mysite.com> |
| Project Key | JIRAPROJECTKEY |
| Issue Type | Myissuetype |
| Jira Username | myjirauser |
| Jira Password | myjirapass |

## LINE Notify

[LINE Notify](https://notify-bot.line.me/)

[LINE Notify API Document](https://notify-bot.line.me/doc/)

**Example:**

| Config | Example |
| ------ | ------- |
| Token | AbCdEf12345 |

## Mail

The E-Mail transports uses the same email-configuration like the rest of LibreNMS.
As a small reminder, here is it's configuration directives including defaults:

**Example:**

| Config | Example |
| ------ | ------- |
| Email | me@example.com |

## Matrix

For using the Matrix transports, you have to create a room on the Matrix-server.
The provided Auth_token belongs to an user, which is member of this room.
The Message, sent to the matrix-room can be built from the variables defined in
[Template-Syntax](Templates.md#syntax) but without the 'alert->' prefix.
See API-Transport. The variable ``` $msg ``` is contains the result of the Alert template.
The Matrix-Server URL is cutted before the beginning of the ``_matrix/client/r0/...`` API-part.

**Example:**

| Config | Example |
| ------ | ------- |
| Matrix-Server URL | <https://matrix.example.com/> |
| Room | !ajPbbPalmVbNuQoBDK:example.com |
| Auth_token: | MDAyYmxvY2F0aW9uI...z1DCn6lz_uOhtW3XRICg |
| Message: | Alert: {{ $msg }} https://librenms.example.com |

## Microsoft Teams

LibreNMS can send alerts to Microsoft Teams [Incoming Webhooks](https://docs.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook) which are
then posted to a specific channel. Microsoft recommends using
[markdown](https://docs.microsoft.com/en-us/microsoftteams/platform/task-modules-and-cards/cards/cards-format#markdown-formatting-for-connector-cards) formatting for connector cards.
Administrators can opt to [compose](https://messagecardplayground.azurewebsites.net/)
the [MessageCard](https://docs.microsoft.com/en-us/outlook/actionable-messages/message-card-reference)
themselves using JSON to get the full functionality.

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://outlook.office365.com/webhook/123456789> |
| Use JSON? | x |

## Nagios Compatible

The nagios transport will feed a FIFO at the defined location with the
same format that nagios would. This allows you to use other alerting
systems with LibreNMS, for example [Flapjack](http://flapjack.io).

**Example:**

| Config | Example |
| ------ | ------- |
| Nagios FIFO | /path/to/my.fifo |

## OpsGenie

Using OpsGenie LibreNMS integration, LibreNMS forwards alerts to
OpsGenie with detailed information. OpsGenie acts as a dispatcher for
LibreNMS alerts, determines the right people to notify based on
on-call schedules and notifies via email, text messages (SMS), phone
calls and iOS & Android push notifications. Then escalates alerts
until the alert is acknowledged or closed.

Create a [LibreNMS
Integration](https://docs.opsgenie.com/docs/librenms-integration) from
the integrations page  once you signup. Then copy the API key from OpsGenie to LibreNMS.

If you want to automatically ack and close alerts, leverage Marid
integration. More detail with screenshots is available in
[OpsGenie LibreNMS Integration page](https://docs.opsgenie.com/docs/librenms-integration).

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://url/path/to/webhook> |

## osTicket

LibreNMS can send alerts to osTicket API which are then converted to osTicket tickets.

**Example:**

| Config | Example |
| ------ | ------- |
| API URL | <http://osticket.example.com/api/http.php/tickets.json> |
| API Token | 123456789 |

## PagerDuty

LibreNMS can make use of PagerDuty, this is done by utilizing an API
key and Integraton Key.

API Keys can be found under 'API Access' in the PagerDuty portal.

Integration Keys can be found under 'Integration' for the particular
Service you have created in the PagerDuty portal.

**Example:**

| Config | Example |
| ------ | ------- |
| API Key | randomsample |
| Integration Key | somerandomstring |

## Philips Hue

Want to spice up your noc life? LibreNMS will flash all lights
connected to your philips hue bridge whenever an alert is triggered.

To setup, go to the you <http://`your-bridge-ip`/debug/clip.html>

- Update the "URL:" field to `/api`
- Paste this in the "Message Body" {"devicetype":"librenms"}
- Press the round button on your `philips Hue Bridge`
- Click on `POST`
- In the `Command Response` You should see output with your
  username. Copy this without the quotes

More Info: [Philips Hue Documentation](https://www.developers.meethue.com/documentation/getting-started)

**Example:**

| Config | Example |
| ------ | ------- |
| Host | http://your-bridge-ip |
| Hue User | username |
| Duration | 1 Second |

## PlaySMS

PlaySMS is an open source SMS-Gateway that can be used via their HTTP
API using a Username and WebService Token. Please consult PlaySMS's
documentation regarding number formatting.

[PlaySMS Docs](https://github.com/antonraharja/playSMS/blob/master/documents/development/WEBSERVICES.md)

Here an example using 3 numbers, any amount of numbers is supported:

**Example:**

| Config | Example |
| ------ | ------- |
| PlaySMS | <https://localhost/index.php?app=ws> |
| User | user1 |
| Token | MYFANCYACCESSTOKEN |
| From | My Name |
| Mobiles | +1234567892,+1234567890,+1234567891 |

## Pushbullet

Get your Access Token from your Pushbullet's settings page and set it in your transport:

**Example:**

| Config | Example |
| ------ | ------- |
| Access Token | MYFANCYACCESSTOKEN |

## Pushover

If you want to change the default [notification
sound](https://pushover.net/api#sounds) for all notifications then you
can add the following in Pushover Options:

`sound=falling`

You also have the possibility to change sound per severity:
`sound_critical=falling`
`sound_warning=siren`
`sound_ok=magic`

Enabling Pushover support is fairly easy, there are only two required parameters.

Firstly you need to create a new Application (called LibreNMS, for
example) in your account on the Pushover website ([https://pushover.net/apps](https://pushover.net/apps)).

Now copy your API Key and obtain your User Key from the newly created
Application and setup the transport.

[Pushover Docs](https://pushover.net/api)

**Example:**

| Config | Example |
| ------ | ------- |
| Api Key | APPLICATIONAPIKEYGOESHERE |
| User Key | USERKEYGOESHERE |
| Pushover Options | sound_critical=falling <br/> sound_warning=siren <br/> sound_ok=magic |

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

## Sensu

The Sensu transport will POST an
[Event](https://docs.sensu.io/sensu-go/latest/reference/events/) to the
[Agent API](https://docs.sensu.io/sensu-go/latest/reference/agent/#create-monitoring-events-using-the-agent-api)
upon an alert being generated.

It will be categorised (ok, warning or critical), and if you configure the
alert to send recovery notifications, Sensu will also clear the alert
automatically. No configuration is required - as long as you are running the
Sensu Agent on your poller with the HTTP socket enabled on tcp/3031, LibreNMS
will start generating Sensu events as soon as you create the transport.

Acknowledging alerts within LibreNMS is not directly supported, but an
annotation (`acknowledged`) is set, so a mutator or silence, or even the
handler could be written to look for it directly in the handler. There is also
an annotation (`generated-by`) set, to allow you to treat LibreNMS events
differently from agent events.

The 'shortname' option is a simple way to reduce the length of device names in
configs. It replaces the last 3 domain components with single letters (e.g.
websrv08.dc4.eu.corp.example.net gets shortened to websrv08.dc4.eu.cen).

### Limitations

- Only a single namespace is supported
- Sensu will reject rules with special characters - the Transport will attempt
to fix up rule names, but it's best to stick to letters, numbers and spaces
- The transport only deals in absolutes - it ignores the got worse/got better
states
- The agent will buffer alerts, but LibreNMS will not - if your agent is
offline, alerts will be dropped
- There is no backchannel between Sensu and LibreNMS - if you make changes in
Sensu to LibreNMS alerts, they'll be lost on the next event (silences will work)

**Example:**

| Config          | Example               |
| --------------- | --------------------- |
| Sensu Endpoint  | http://localhost:3031 |
| Sensu Namespace | eu-west               |
| Check Prefix    | lnms                  |
| Source Key      | hostname              |

## Slack

The Slack transport will POST the alert message to your Slack Incoming
WebHook using the attachments option, you are able to specify multiple
webhooks along with the relevant options to go with it. Simple html
tags are stripped from the message. All options are optional, the
only required value is for url, without this  then no call to Slack will be made.

We currently support the following attachment options:

`author_name`

[Slack docs](https://api.slack.com/docs/message-attachments)

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | <https://slack.com/url/somehook> |
| Slack Options | author_name=Me |

## SMSEagle

SMSEagle is a hardware SMS Gateway that can be used via their HTTP API
using a Username and password.

Destination numbers are one per line, with no spaces. They can be in
either local or international dialling format.

[SMSEagle Docs](http://www.smseagle.eu)

**Example:**

| Config | Example |
| ------ | ------- |
| SMSEagle Host | ip.add.re.ss |
| User | smseagle_user |
| Password | smseagle_user_password |
| Mobiles | +3534567890 <br/> 0834567891 |

## SMSmode
SMSmode is a SMS provider that can be configured by using the generic API Transport.
You need a token you can find on your personnal space.

[SMSmode docs](https://www.smsmode.com/pdf/fiche-api-http.pdf)

**Example:**

| Config | Example |
| ------ | ------- |
| Transport type | Api |
| API Method | POST |
| API URL | http://api.smsmode.com/http/1.6/sendSMS.do |
| Options | accessToken=_PUT_HERE_YOUR_TOKEN_<br />numero=_PUT_HERE_DESTS_NUMBER_COMMA_SEPARATED_<br />message={{ $msg }} |

## Splunk

LibreNMS can send alerts to a Splunk instance and provide all device
and alert details.

Example output:

```
Feb 21 15:21:52 nms  hostname="localhost", sysName="localhost", 
sysDescr="", sysContact="", os="fortigate", type="firewall", ip="localhost", 
hardware="FGT_50E", version="v5.6.9", serial="", features="", location="", 
uptime="387", uptime_short=" 6m 27s", uptime_long=" 6 minutes 27 seconds", 
description="", notes="", alert_notes="", device_id="0", rule_id="0", 
id="0", proc="", status="1", status_reason="", ping_timestamp="", ping_loss="0", 
ping_min="25.6", ping_max="26.8", ping_avg="26.3", 
title="localhost recovered from  Device up/down  ", elapsed="14m 54s", uid="0", 
alert_id="0", severity="critical", name="Device up/down", 
timestamp="2020-02-21 15:21:33", state="0", device_device_id="0", 
device_inserted="", device_hostname="localhost", device_sysName="localhost", 
device_ip="localhost", device_overwrite_ip="", device_timeout="", device_retries="", 
device_snmp_disable="0", device_bgpLocalAs="0", 
device_sysObjectID="", device_sysDescr="", 
device_sysContact="", device_version="v5.6.9", device_hardware="FGT_50E", 
device_features="build1673", device_location_id="", device_os="fortigate", 
device_status="1", device_status_reason="", device_ignore="0", device_disabled="0", 
device_uptime="387", device_agent_uptime="0", device_last_polled="2020-02-21 15:21:33", 
device_last_poll_attempted="", device_last_polled_timetaken="7.9", 
device_last_discovered_timetaken="11.77", device_last_discovered="2020-02-21 13:16:42", 
device_last_ping="2020-02-21 15:21:33", device_last_ping_timetaken="26.3", 
device_purpose="", device_type="firewall", device_serial="FGT50EXXX", 
device_icon="images/os/fortinet.svg", device_poller_group="0", 
device_override_sysLocation="0", device_notes="", device_port_association_mode="1", 
device_max_depth="0", device_disable_notify="0", device_location="", 
device_vrf_lites="Array", device_lat="", device_lng="", - 
sysObjectID => ""; `

Each alert will be sent as a separate message.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| UDP Port | 514 |

## Syslog

You can have LibreNMS emit alerts as syslogs complying with RFC 3164.

More information on RFC 3164 can be found here:
[https://tools.ietf.org/html/rfc3164](https://tools.ietf.org/html/rfc3164)

Example output: `<26> Mar 22 00:59:03 librenms.host.net librenms[233]:
[Critical] network.device.net: Port Down - port_id => 98939; ifDescr => xe-1/1/0;`

Each fault will be sent as a separate syslog.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| Port | 514 |
| Facility | 3 |

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

**Example:**

| Config | Example |
| ------ | ------- |
| Chat ID | 34243432 |
| Token | 3ed32wwf235234 |
| Format | HTML or MARKDOWN |

## Twilio SMS

Twilio will send your alert via SMS.  From your Twilio account you
will need your account SID, account token and your Twilio SMS phone
number that you would like to send the alerts from.  Twilio's APIs are
located at: [https://www.twilio.com/docs/api?filter-product=sms](https://www.twilio.com/docs/api?filter-product=sms)

**Example:**

| Config | Example |
| ------ | ------- |
| SID | ACxxxxxxxxxxxxxxxxxxxxxxxxxxxx |
| Token | 7xxxx573acxxxbc2xxx308d6xxx652d32 |
| Twilio SMS Number | 8888778660 |

## UKFast PSS

UKFast PSS tickets can be raised from alerts using the UKFastPSS transport. This required an [API key](https://my.ukfast.co.uk/applications) with PSS `write` permissions

**Example:**

| Config | Example |
| ------ | ------- |
| API Key | ABCDefgfg12 |
| Author | 5423 |
| Priority | Critical |
| Secure | true |

## VictorOps

VictorOps provide a webHook url to make integration extremely
simple. To get the URL required login to your VictorOps  account and go to:

Settings -> Integrations -> REST Endpoint -> Enable Integration.

The URL provided will have $routing_key at the end, you need to change
this to something that is unique to the system  sending the alerts
such as librenms. I.e:

`https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms`

**Example:**

| Config | Example |
| ------ | ------- |
| Post URL | <https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms> |

## Kayako Classic

LibreNMS can send alerts to Kayako Classic API which are then
converted to tickets. To use this module, you need REST API feature
enabled in Kayako Classic and configured email account at LibreNMS. To
enable this, do this:

AdminCP -> REST API -> Settings -> Enable API (Yes)

Also you need to know the department id to provide tickets to
appropriate department and a user email to provide, which is used as
ticket author.  To get department id: navigate to appropriate
department name at the departments list page in Admin CP and watch the
number at the end of url. Example:
<http://servicedesk.example.com/admin/Base/Department/Edit/17>. Department
ID is 17

As a requirement, you have to know API Url, API Key and API Secret to
connect to servicedesk

[Kayako REST API Docs](https://classic.kayako.com/article/1502-kayako-rest-api)

**Example:**

| Config | Example |
| ------ | ------- |
| Kayako URL | <http://servicedesk.example.com/api/> |
| Kayako API Key | 8cc02f38-7465-4a0c-8730-bb3af122167b |
| Kayako API Secret | Y2NhZDIxNDMtNjVkMi0wYzE0LWExYTUtZGUwMjJiZDI0ZWEzMmRhOGNiYWMtNTU2YS0yODk0LTA1MTEtN2VhN2YzYzgzZjk5 |
| Kayako Department | 1 |

## Signal CLI

Use the Signal Mesenger for Alerts. Run the Signal CLI with the D-Bus option.

[GitHub Project](https://github.com/AsamK/signal-cli)

**Example:**

| Config | Example |
| ------ | ------- |
| Path | /opt/signal-cli/bin/signal-cli |
| Recipient type | Group |
| Recipient | dfgjsdkgljior4345== |

## SMSFeedback

SMSFeedback is a SAAS service, which can be used to deliver Alerts via API, using API url, Username & Password.

They can be in international dialling format only.

[SMSFeedback Api Docs](https://www.smsfeedback.ru/smsapi/)

**Example:**

| Config | Example |
| ------ | ------- |
| User | smsfeedback_user |
| Password | smsfeedback_password |
| Mobiles | 71234567890 |
| Sender name| CIA |
