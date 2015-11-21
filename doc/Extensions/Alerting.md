Table of Content:

- [About](#about)
- [Rules](#rules)
    - [Syntax](#rules-syntax)
    - [Examples](#rules-examples)
- [Templates](#templates)
    - [Syntax](#templates-syntax)
    - [Examples](#templates-examples)
- [Transports](#transports)
    - [E-Mail](#transports-email)
    - [API](#transports-api)
    - [Nagios-Compatible](#transports-nagios)
    - [IRC](#transports-irc)
    - [Slack](#transports-slack)
    - [HipChat](#transports-hipchat)
    - [PagerDuty](#transports-pagerduty)
    - [Pushover](#transports-pushover)
    - [Boxcar](#transports-boxcar)
    - [Pushbullet](#transports-pushbullet)
    - [Clickatell](#transports-clickatell)
    - [PlaySMS](#transports-playsms)
    - [VictorOps](#transports-victorops)
- [Entities](#entities)
    - [Devices](#entity-devices)
    - [BGP Peers](#entity-bgppeers)
    - [IPSec Tunnels](#entity-ipsec)
    - [Memory Pools](#entity-mempools)
    - [Ports](#entity-ports)
    - [Processors](#entity-processors)
    - [Storage](#entity-storage)
- [Macros](#macros)
    - [Device](#macros-device)
    - [Port](#macros-port)
    - [Time](#macros-time)


# <a name="about">About</a>

LibreNMS includes a highly customizable alerting system.  
The system requires a set of user-defined rules to evaluate the situation of each device, port, service or any other entity.

> You can configure all options for alerting and transports via the WebUI, config options in this document are crossed out but left for reference.

This document only covers the usage of it. See the [DEVELOPMENT.md](https://github.com/f0o/glowing-tyrion/blob/master/DEVELOPMENT.md) for code-documentation.

# <a name="rules">Rules</a>

Rules are defined using a logical language.  
The GUI provides a simple way of creating basic as well as complex Rules in a self-describing manner.  
More complex rules can be written manually.

## <a name="rules-syntax">Syntax</a>

Rules must consist of at least 3 elements: An __Entity__, a __Condition__ and a __Value__.  
Rules can contain braces and __Glues__.  
__Entities__ are provided as `%`-Noted pair of Table and Field. For Example: `%ports.ifOperStatus`.  
__Conditions__ can be any of:

- Equals `=`
- Not Equals `!=`
- Matches `~`
- Not Matches `!~`
- Greater `>`
- Greater or Equal `>=`
- Smaller `<`
- Smaller or Equal `<=`

__Values__ can be Entities or any single-quoted data.  
__Glues__ can be either `&&` for `AND` or `||` for `OR`.

__Note__: The difference between `Equals` and `Matches` (and its negation) is that `Equals` does a strict comparison and `Matches` allows the usage of RegExp.  
Arithmetics are allowed as well.

## <a name="rules-examples">Examples</a>

Alert when:

- Device goes down: `%devices.status != '1'`
- Any port changes: `%ports.ifOperStatus != 'up'`
- Root-directory gets too full: `%storage.storage_descr = '/' && %storage.storage_perc >= '75'`
- Any storage gets fuller than the 'warning': `%storage.storage_perc >= %storage_perc_warn`

# <a name="templates">Templates</a>

Templates can be assigned to a single or a group of rules.  
They can contain any kind of text.  
The template-parser understands `if` and `foreach` controls and replaces certain placeholders with information gathered about the alert.  

## <a name="templates-syntax">Syntax</a>

Controls:

- if-else (Else can be omitted):  
`{if %placeholder == 'value'}Some Text{else}Other Text{/if}`
- foreach-loop:  
`{foreach %placeholder}Key: %key<br/>Value: %value{/foreach}`

Placeholders:

- Hostname of the Device: `%hostname`
- Title for the Alert: `%title`
- Time Elapsed, Only available on recovery (`%state == 0`): `%elapsed`
- Alert-ID: `%id`
- Unique-ID: `%uid`
- Faults, Only available on alert (`%state != 0`), must be iterated in a foreach (`{foreach %faults}`). Holds all available information about the Fault, accessable in the format `%value.Column`, for example: `%value.ifDescr`. Special field `%value.string` has most Identification-information (IDs, Names, Descrs) as single string, this is the equivalent of the default used.
- State: `%state`
- Severity: `%severity`
- Rule: `%rule`
- Rule-Name: `%name`
- Timestamp: `%timestamp`
- Contacts, must be iterated in a foreach, `%key` holds email and `%value` holds name: `%contacts`

The Default Template is a 'one-size-fit-all'. We highly recommend defining own templates for your rules to include more specific information.
Templates can be matched against several rules.

## <a name="templates-examples">Examples</a>

Default Template:  
```text
%title\r\n
Severity: %severity\r\n
{if %state == 0}Time elapsed: %elapsed\r\n{/if}
Timestamp: %timestamp\r\n
Unique-ID: %uid\r\n
Rule: {if %name}%name{else}%rule{/if}\r\n
{if %faults}Faults:\r\n
{foreach %faults}  #%key: %value.string\r\n{/foreach}{/if}
Alert sent to: {foreach %contacts}%value <%key> {/foreach}
```

# <a name="transports">Transports</a>

Transports are located within `$config['install_dir']/includes/alerts/transports.*.php` and defined as well as configured via ~~`$config['alert']['transports']['Example'] = 'Some Options'`~~.  

Contacts will be gathered automatically and passed to the configured transports.  
By default the Contacts will be only gathered when the alert triggers and will ignore future changes in contacts for the incident. If you want contacts to be re-gathered before each dispatch, please set ~~`$config['alert']['fixed-contacts'] = false;`~~ in your config.php.

The contacts will always include the `SysContact` defined in the Device's SNMP configuration and also every LibreNMS-User that has at least `read`-permissions on the entity that is to be alerted.  
At the moment LibreNMS only supports Port or Device permissions.  
You can exclude the `SysContact` by setting:
~~
```php
$config['alert']['syscontact'] = false;
```
~
To include users that have `Global-Read` or `Administrator` permissions it is required to add these additions to the `config.php` respectively:
~
```php
$config['alert']['globals'] = true; //Include Global-Read into alert-contacts
$config['alert']['admins']  = true; //Include Administrators into alert-contacts
```
~~

## <a name="transports-email">E-Mail</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

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

The Slack transport will POST the alert message to your Slack Incoming WebHook, you are able to specify multiple webhooks along with the relevant options to go with it. All options are optional, the only required value is for url, without this then no call to Slack will be made. Below is an example of how to send alerts to two channels with different customised options:

~~
```php
$config['alert']['transports']['slack'][] = array('url' => "https://hooks.slack.com/services/A12B34CDE/F56GH78JK/L901LmNopqrSTUVw2w3XYZAB4C", 'channel' => '#Alerting');

$config['alert']['transports']['slack'][] = array('url' => "https://hooks.slack.com/services/A12B34CDE/F56GH78JK/L901LmNopqrSTUVw2w3XYZAB4C", 'channel' => '@john', 'username' => 'LibreNMS', 'icon_emoji' => ':ghost:');

```
~~

## <a name="transports-hipchat">HipChat</a>

> You can configure these options within the WebUI now, please avoid setting these options within config.php

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

## <a name="transports-pushbullet">Pushbullet</a>

Enabling Pushbullet is a piece of cake.
Get your Access Token from your Pushbullet's settings page and set it in your config like:

~~
```php
$config['alert']['transports']['pushbullet'] = 'MYFANCYACCESSTOKEN';
```
~~

## <a name="transports-clickatell">Clickatell</a>

Clickatell provides a REST-API requiring an Authorization-Token and at least one Cellphone number.  
Please consult Clickatell's documentation regarding number formating.  
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

PlaySMS is an OpenSource SMS-Gateway that can be used via their HTTP-API using a Username and WebService-Token.  
Please consult PlaySMS's documentation regarding number formating.  
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

VictorOps provide a webHook url to make integration extremely simple. To get the URL required login to your VictorOps account and go to:

Settings -> Integrations -> REST Endpoint -> Enable Integration.

The URL provided will have $routing_key at the end, you need to change this to something that is unique to the system sending the alerts such as librenms. I.e:

`https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms`

~~
```php
$config['alert']['transports']['victorops']['url'] = 'https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms';
```
~~

# <a name="entities">Entities

Entities as described earlier are based on the table and column names within the database, if you are unsure of what the entity is you want then have a browse around inside MySQL using `show tables` and `desc <tablename>`.

## <a name="entity-devices">Devices</a>

__devices.hostname__ = The devices hostname.

__devices.location__ = The devices location.

__devices.status__ = The status of the device, 1 = up, 0 = down.

__devices.status_reason__ = The reason the device was detected as down (icmp or snmp).

__devices.ignore__ = If the device is ignored this will be set to 1.

__devices.disabled__ = If the device is disabled this will be set to 1.

__devices.last_polled__ = The the last polled datetime (yyyy-mm-dd hh:mm:ss).

__devices.type__ = The device type such as network, server, firewall, etc.

## <a name="entity-bgppeers">BGP Peers</a>

__bgpPeers.astext__ = This is the description of the BGP Peer.

__bgpPeers.bgpPeerIdentifier__ = The IP address of the BGP Peer.

__bgpPeers.bgpPeerRemoteAs__ = The AS number of the BGP Peer.

__bgpPeers.bgpPeerState__ = The operational state of the BGP session.

__bgpPeers.bgpPeerAdminStatus__ = The administrative state of the BGP session.

__bgpPeers.bgpLocalAddr__ = The local address of the BGP session.

## <a name="entity-ipsec">IPSec Tunnels</a>

__ipsec_tunnels.peer_addr__ = The remote VPN peer address.

__ipsec_tunnels.local_addr__ = The local VPN address.

__ipsec_tunnels.tunnel_status__ = The VPN tunnels operational status.

## <a name="entity-mempools">Memory pools</a>

__mempools.mempool_type__ = The memory pool type such as hrstorage, cmp and cemp.

__mempools.mempool_descr__ = The description of the pool such as Physical memory, Virtual memory and System memory.

__mempools.mempool_perc__ = The used percentage of the memory pool.

## <a name="entity-ports">Ports</a>

__ports.ifDescr__ = The interface description.

__ports.ifName__ = The interface name.

__ports.ifSpeed__ = The port speed in bps.

__ports.ifHighSpeed__ = The port speed in mbps.

__ports.ifOperStatus__ = The operational status of the port (up or down).

__ports.ifAdminStatus__ = The administrative status of the port (up or down).

__ports.ifDuplex__ = Duplex setting of the port.

__ports.ifMtu__ = The MTU setting of the port.

## <a name="entity-processors">Processors</a>

__processors.processor_usage__ = The usage of the processor as a percentage.

__processors.processor_descr__ = The description of the processor.

## <a name="entity-storage">Storage</a>

__storage.storage_descr__ = The description of the storage.

__storage.storage_perc__ = The usage of the storage as a percentage.

# <a name="macros">Macros</a>

Macros are shorthands to either portion of rules or pure SQL enhanced with placeholders.
You can define your own macros in your `config.php`.

Example macro-implementation of Debian-Devices
```php
$config['alert']['macros']['rule']['is_debian'] = '%devices.features ~ "@debian@"';
```
And in the Rule:
```
...  && %macros.is_debian = "1" && ...
```

This Example-macro is a Boolean-macro, it applies a form of filter to the set of results defined by the rule.
All macros that are not unary should return Boolean.

You can only apply _Equal_ or _Not-Equal_ Operations on Bollean-macros where `True` is represented by `"1"` and `False` by `"0"`.


## <a name="macros-device">Device</a> (Boolean)

Entity: `%macros.device`

Description: Only select devices that aren't deleted, ignored or disabled.

Source: `(%devices.disabled = "0" && %devices.ignore = "0")`

### <a name="macros-device-up">Device is up</a> (Boolean)

Entity: `%macros.device_up`

Description: Only select devices that are up.

Implies: %macros.device

Source: `(%devices.status = "1" && %macros.device)`

### <a name="macros-device-down">Device is down</a> (Boolean)

Entity: `%macros.device_down`

Description: Only select devices that are down.

Implies: %macros.device

Source: `(%devices.status = "0" && %macros.device)`

## <a name="macros-port">Port</a> (Boolean)

Entity: `%macros.port`

Description: Only select ports that aren't deleted, ignored or disabled.

Source: `(%ports.deleted = "0" && %ports.ignore = "0" && %ports.disabled = "0")`

### <a name="macros-port-up">Port is up</a> (Boolean)

Entity: `%macros.port_up`

Description: Only select ports that are up and also should be up.

Implies: %macros.port

Source: `(%ports.ifOperStatus = "up" && %ports.ifAdminStatus = "up" && %macros.port)`

### <a name="macros-port-down">Port is down</a> (Boolean)

Entity: `%macros.port_down`

Description: Only select ports that are down.

Implies: %macros.port

Source: `(%ports.ifOperStatus = "down" && %ports.ifAdminStatus != "down" && %macros.port)`

### <a name="macros-port-usage-perc">Port-Usage in Percent</a> (Decimal)

Entity: `%macros.port_usage_perc`

Description: Return port-usage in percent.

Source: `((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100`

## <a name="macros-time">Time</a>

### <a name="macros-time-now">Now</a> (Datetime)

Entity: `%macros.now`

Description: Alias of MySQL's NOW()

Source: `NOW()`

### <a name="macros-time-past-Nm">Past N Minutes</a> (Datetime)

Entity: `%macros.past_$m`

Description: Returns a MySQL Timestamp dated `$` Minutes in the past. `$` can only be a supported Resolution.

Example: `%macros.past_5m` is Last 5 Minutes.

Resolution: 5,10,15,30,60

Source: `DATE_SUB(NOW(),INTERVAL $ MINUTE)`

## <a name="macros-sensors">Sensors</a> (Boolean)

Entity: `%macros.sensor`

Description: Only select sensors that aren't ignored.

Source: `(%sensors.sensor_alert = 1)`

## <a name="macros-packetloss">Packet Loss</a> (Boolean)

Entity: `(%macros.packet_loss_5m)`

Description: Packet loss % value for the device within the last 5 minutes.

Example: `%macros.packet_loss_5m` > 50

Entity: `(%macros.packet_loss_15m)`

Description: Packet loss % value for the device within the last 15 minutes.

Example: `%macros.packet_loss_15m` > 50


