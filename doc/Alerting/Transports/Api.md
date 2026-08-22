## API

The API transport reaches any service provider with a POST, PUT, or GET
URL. One example is an SMS provider. There are several ways to use it:

- The variable `$msg` holds the text from the alert template. You can
  send this variable as an option to the API. An HTTP GET request
  usually has a length limit.

- The API option fields accept the variables from
  [Template-Syntax](../Templates.md#syntax), without the `alert->`
  prefix. For example, `$alert->uptime` is `$uptime` in the API
  transport.

- The API headers field adds the headers of the API endpoint.

- The API body field sends data in the format of the API endpoint.

- Send as form. This option sends the body content as URL-encoded form
  data. Enable it when your endpoint needs key=value pairs. Make sure
  that your variables hold no newline. `$msg` often holds newlines.

These are some common variables:

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

The example below uses the API `sms-api` of `my.example.com`. It sends
the title of the alert to the given number with the given service key.
For the correct configuration, read the documentation of your service.

| Config | Example |
| ------ | ------- |
| API Method    | GET |
| API URL       | <http://my.example.com/sms-api>
| API Options   | rcpt=0123456789 <br/> key=0987654321abcdef <br/> msg=(LNMS) {{ $title }} |
| API Username  | myUsername |
| API Password  | myPassword |

The example below uses the API `wall-display` of `my.example.com`. It
sends the title and the text of the alert to a screen in the network
operation center.

| Config | Example |
| ------ | ------- |
| API Method    | POST |
| API URL       | <http://my.example.com/wall-display>
| API Options   | title={{ $title }} <br/> msg={{ $msg }}|

The example below uses the API `component` of `my.example.com` with id
1. The body holds a JSON status value. The headers hold the token
authentication and the necessary content type.

| Config | Example |
| ------ | ------- |
| API Method    | PUT |
| API URL       | http://my.example.com/comonent/1
| API Headers   | X-Token=HASH
|               | Content-Type=application/json
| API Body      | { "status": 2 }