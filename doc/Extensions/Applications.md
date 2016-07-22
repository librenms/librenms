Application Monitoring
----------------------

LibreNMS supports monitoring application statistics through two methods the [Agent](http://docs.librenms.org/Extensions/Agent-Setup/) or direct connection to the device.

To enable the agent go to device settings -> applications and enable the "unix-agent"
If you are using direct connection to collect statistics, you must manually enable the desired apps in the device settings -> applications page.

##Applications

###PowerDNS Recursor
A recursive DNS sever: https://www.powerdns.com/recursor.html
#### Connection
The LibreNMS polling host must be able to connect to port 8082 on the monitored device.
The web-server must be enabled, see the Recursor docs: https://doc.powerdns.com/md/recursor/settings/#webserver
There is currently no way to specify a custom port or password.
#### Agent
Copy powerdns-recursor to the `/usr/lib/check_mk_agent/local` directory.
The user check_mk is running as must be able to run `rec_control get-all`