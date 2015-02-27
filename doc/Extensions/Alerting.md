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

# <a name="about">About</a>

LibreNMS includes a highly customizable alerting system.  
The system requires a set of user-defined rules to evaluate the situation of each device, port, service or any other entity.

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

__Note__: The difference between `Equals` and `Matches` (and it's negation) is that `Equals` does a strict comparison and `Matches` allows the usage of the placeholder `@`. The placeholder `@` is comparable with `.*` in RegExp.  
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

Transports are located within `$config['install_dir']/includes/alerts/transports.*.php` and defined as well as configured via `$config['alert']['transports']['Example'] = 'Some Options'`.  

Contacts will be gathered automatically and passed to the configured transports.  
The contacts will always include the `SysContact` defined in the Device's SNMP configuration and also every LibreNMS-User that has at least `read`-permissions on the entity that is to be alerted.  
At the moment LibreNMS only supports Port or Device permissions.  
To include users that have `Global-Read` or `Administrator` permissions it is required to add these additions to the `config.php` respectively:
```php
$config['alert']['globals'] = true; //Include Global-Read into alert-contacts
$config['alert']['admins']  = true; //Include Adminstrators into alert-contacts
```

## <a name="transports-email">E-Mail</a>

E-Mail transport is enabled with adding the following to your `config.php`:  
```php
$config['alert']['transports']['mail'] = true;
```

The E-Mail transports uses the same email-configuration like the rest of LibreNMS.  
As a small reminder, here is it's configuration directives including defaults:
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

$config['alerts']['email']['default_only'] = FALSE;                // Only send alerts to default-contact
$config['alerts']['email']['default']      = NULL;                 // Default-Contact
```

## <a name="transports-api">API</a>

API transports definitions are a bit more complex than the E-Mail configuration.  
The basis for configuration is `$config['alert']['transports']['api'][METHOD]` where `METHOD` can be `get`,`post` or `put`.  
This basis has to contain an array with URLs of each API to call.  
The URL can have the same placeholders as defined in the [Template-Syntax](#templates-syntax).  
If the `METHOD` is `get`, all placeholders will be URL-Encoded.  
The API transport uses cURL to call the APIs, therefore you might need to install `php5-curl` or similar in order to make it work.  
__Note__: it is highly recommended to define own [Templates](#templates) when you want to use the API transport. The default template might exceed URL-length for GET requests and therefore cause all sorts of errors.  

Example:
```php
$config['alert']['transports']['api']['get'][] = "https://api.thirdparti.es/issue?apikey=abcdefg&subject=%title";
```

## <a name="transports-nagios">Nagios Compatible</a>

The nagios transport will feed a FIFO at the defined location with the same format that nagios would.  
This allows you to use other Alerting-Systems to work with LibreNMS, for example [Flapjack](http://flapjack.io).
```php
$config['alert']['transports']['nagios'] = "/path/to/my.fifo"; //Flapjack expects it to be at '/var/cache/nagios3/event_stream.fifo'
```

## <a name="transports-irc">IRC</a>

The IRC transports only works together with the LibreNMS IRC-Bot.  
Configuration of the LibreNMS IRC-Bot is described [here](https://github.com/librenms/librenms/blob/master/doc/Extensions/IRC-Bot.md).  
```php
$config['alert']['transports']['irc'] = true;
```

## <a name="transports-slack">Slack</a>

The Slack transport will POST the alert message to your Slack Incoming WebHook, you are able to specify multiple webhooks along with the relevant options to go with it. All options are optional, the only required value is for url, without this then no call to Slack will be made. Below is an example of how to send alerts to two channels with different customised options:

```php
$config['alert']['transports']['slack']['post'][] = array('url' => "https://hooks.slack.com/services/A12B34CDE/F56GH78JK/L901LmNopqrSTUVw2w3XYZAB4C", 'channel' => '#Alerting');

$config['alert']['transports']['slack']['post'][] = array('url' => "https://hooks.slack.com/services/A12B34CDE/F56GH78JK/L901LmNopqrSTUVw2w3XYZAB4C", 'channel' => '@john', 'username' => 'LibreNMS', 'icon_emoji' => ':ghost:');

```
