# Privoxy

For this to work, the following log items need enabled for Privoxy.

```
debug     2 # show each connection status
debug   512 # Common Log Format
debug  1024 # Log the destination for requests Privoxy didn't let through, and the reason why.
debug  4096 # Startup banner and warnings
debug  8192 # Non-fatal errors
```

## Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl libfile-readbackwards-perl libipc-run3-perl cpanminus
    cpanm Time::Piece
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-MIME-Base64 p5-File-Slurp p5-File-ReadBackwards p5-IPC-Run3 p5-Time-Piece
    ```
=== "Generic"

    ```bash
    cpanm Time::Piece JSON MIME::Base64 File::Slurp File::ReadBackwards IPC::Run3
    ```

## SNMP Extend

1. Download the extend and make sure it is executable.
```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/privoxy -O /etc/snmp/privoxy
chmod +x /etc/snmp/privoxy
```

2. Add the extend to snmpd.conf and restart snmpd.

```bash
extend privoxy /etc/snmp/privoxy
```

If your logfile is not at `/var/log/privoxy/logfile`, that may be
changed via the `-f` option.

If `privoxy-log-parser.pl` is not found in your standard `$PATH`
setting, you may will need up call the extend via `/usr/bin/env` with
a `$PATH` set to something that includes it.

Once that is done, just wait for the server to be rediscovered or just
enable it manually.

## Cron

If you are having timeouts or there is privelege seperation issues,
then it can be ran via cron like below. `-w` can be used to write it
out and `-o` can be used to control where it is written to. See
`--help` for more information.

Add the following to your `/etc/crontab.d/librenms_privoxy`: 

```bash
*/5 * * * * root /etc/snmp/privoxy -w > /dev/null

```

Add/Change the following to your `/etc/snmp/snmpd.conf`:

```bash
extend privoxy /bin/cat /var/cache/privoxy_extend.json.snmp
```
