## Pushover

To change the default [notification
sound](https://pushover.net/api#sounds) for all notifications, add this
line to Pushover Options:

`sound=falling`

You can also set a sound for each severity. `sound_ok` applies to a
recovery notification:
`sound_critical=falling`
`sound_warning=siren`
`sound_ok=magic`

Pushover support needs only two parameters. First, create a new
application in your account on the [Pushover
website](https://pushover.net/apps). You can give it the name LibreNMS.
Then get the API key of the new application and your user key or group
key. Then configure the transport.

[Pushover Docs](https://pushover.net/api)

**Example:**

| Config | Example |
| ------ | ------- |
| Api Key | APPLICATIONAPIKEYGOESHERE |
| User/Group Key | USERORGROUPKEYGOESHERE |
| Pushover Options | sound_critical=falling <br/> sound_warning=siren <br/> sound_ok=magic |
