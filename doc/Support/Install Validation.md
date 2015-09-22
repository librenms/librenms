Install validation
------------------

With a lot of configuration possibilities and at present the only way to do this being by manually editing config.php then it's not 
uncommon that mistakes get made. It's also impossible to validate user input in config.php when you're just using a text editor :)

So, to try and help with some of the general issues people come across we've put together a simple validation tool which at present will: 

 - Validate config.php from a php perspective including whitespace where it shouldn't be.
 - Connection to your MySQL server to verify credentials.
 - Checks if you are running the older alerting system.
 - Checks your rrd directory setup if not running rrdcached.
 - Checks disk space for where /opt/librenms is installed.
 - Checks location to fping
 - Tests MySQL strict mode being enabled
 - Tests for files not owned by librenms user (if configured)

Optionally you can also pass -m and a module name for that to be tested. Current modules are:

 - mail. This will validate your mail transport configuration.

Output, this is color coded to try and make things a little easier:

Green OK - This is a good thing, you can skip over these :)

Yellow WARN - You probably want to check this out.

Red FAIL - This is going to need your attention!
