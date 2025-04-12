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