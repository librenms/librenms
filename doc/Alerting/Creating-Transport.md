# Creating a new Transport

## File location

All transports are in `LibreNMS\Alert\Transport`. Each file has the
name of its transport. An example is `Discord.php` for Discord.

## Transport structure

A new transport needs these functions to pass the unit tests:

`deliverAlert()` - the alert code calls this function to start the
transport. In this function, prepare the transport configuration for
use.

`contact$Transport()` - this function has the name of the transport.
For Discord, the name is `contactDiscord()`. This function connects to
the third-party API, starts the mail command, or does the action of
your alert.

`configTemplate()` - this function defines the form for the transport
configuration in the web interface. It also defines the validation of
the data. The validation uses [Laravel
validation](https://laravel.com/docs/validation).

## Documentation

Create a documentation file `doc/Alerting/Transports/$Transport.md`
with the details of your new transport.

Add a table with the form values and examples. For example:

|Config | Example|
------ | -------
Discord URL | <https://discordapp.com/api/webhooks/4515489001665127664/82-sf4385ysuhfn34u2fhfsdePGLrg8K7cP9wl553Fg6OlZuuxJGaa1d54fe>|
Options | username=myname|

Add a link to each third-party document that explains the use of the
transport.