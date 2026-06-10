## API

The API transport allows to reach any service provider using POST, PUT or GET URLs
(Like SMS provider, etc). It can be used in multiple ways:

- The same text built from the Alert template is available in the
  variable

`$msg`, which can then be sent as an option to the API. Be carefull
that HTTP GET requests are usually limited in length.

- The API-Option fields can be directly built from the variables
  defined in [Template-Syntax](../Templates.md#syntax) but without the
  'alert->' prefix. For instance, `$alert->uptime` is available as
  `$uptime` in the API transport

- The API-Headers allows you to add the headers that the api endpoint requires.

- The API-body allow sending data in the format required by the API endpoint.

- Send as form. This option allows you to send the body content as form data url encoded. Enable this if your endpoint is expecting fields to be sent as key=value pairs. Please ensure newlines aren't present in any of your variables as can be the case with `$msg`.

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

The example below will use the API named component of my.example.com
with id 1, body as json status value and headers send token
authentication and content type required.

| Config | Example |
| ------ | ------- |
| API Method    | PUT |
| API URL       | http://my.example.com/comonent/1
| API Headers   | X-Token=HASH
|               | Content-Type=application/json
| API Body      | { "status": 2 }