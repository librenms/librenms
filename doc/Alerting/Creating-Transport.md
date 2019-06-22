source: Alerting/Creating-Transport.md
path: blob/master/doc/

# Creating a new Transport

### File location

All transports are located in `LibreNMS\Alert\Transport` and the files
are named after the Transport name. I.e `Discord.php` for Discord.

### Transport structure

The following functions are required for a new transport to pass the unit tests:

`deliverAlert()` - This is function called within alerts to invoke the
transport. Here you should do any post processing of the transport
config to get it ready for use.

`contact$Transport()` - This is named after the transport so for
Discord it would be `contactDiscord()`. This is what actually
interacts with the 3rd party API, invokes the mail command or whatever
you want your alert to do.

`configTemplate()` - This is used to define the form that will accept
the transport config in the webui and then what data should be
validated and how. Validation is done using [Laravel validation](https://laravel.com/docs/5.7/validation)

The following function is __not__ required for new Transports and is
for legacy reasons only. `deliverAlertOld()`.

### Documentation

Please don't forget to update the [Transport](Transports.md) file to
include details of your new transport.

A table should be provided to indicate the form values that we ask for
and examples. I.e:

```
**Example:**
Config | Example
------ | -------
Discord URL | https://discordapp.com/api/webhooks/4515489001665127664/82-sf4385ysuhfn34u2fhfsdePGLrg8K7cP9wl553Fg6OlZuuxJGaa1d54fe
Options | username=myname
```
