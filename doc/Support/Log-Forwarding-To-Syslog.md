# Log Forwarding To Syslog

The log files by LibreNMS under it's logs director can easily be forwarded to syslog via
`filesyslogger` using the config at `misc/filesyslogger.toml`. This config file assumes
`filesyslogger` is being started in the LibreNMS base directory.

By default it is set to use `/dev/log` as the socket to log to.

A service file for systemd can be found at `misc/filesyslogger.service`. The default
service file assumes LibreNMS is installed to `/opt/librenms` and you wish to use the
config file in a unmodified manner. If modifying the config, copy it to like
`/usr/local/etc/librenms-filesyslogger.toml` or the like and update the service file
accordingly.

Configuring this on Linux can be done via the following.

```
# If Debian
apt-get install cpanminus libpoe-perl libtoml-perl

# General
cpanminus Log::Syslog::Fast File::Syslogger
cp misc/filesyslogger.service /etc/system/systemd/librenms-filesyslogger.service
systemd enable librenms-filesyslogger.service
systemd start librenms-filesyslogger.service
```
