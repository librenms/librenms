
# SMART

## Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt-get install smartmontools libjson-perl libmime-base64-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-MIME-Base64 smartmontools
    ```

=== "RedHat/CentOS"

    ```bash
    dnf install smartmontools perl-JSON perl-MIME-Base64
    ```

## SNMP Extend

1. Copy the Perl script, smart, to the desired host.

```bash
wget https://github.com/librenms/librenms-agent/raw/master/snmp/smart-v1 -O /etc/snmp/smart
```

3. Make the script executable

```bash
chmod +x /etc/snmp/smart
```

4. Setup a cronjob to run it. This ensures slow to poll disks won't
   result in errors.

```bash
 */5 * * * * /etc/snmp/smart -u -Z
```

5. Edit your snmpd.conf file and add:

```bash
extend smart /bin/cat /var/cache/smart
```

6. You will also need to create the config file, which defaults to the same path as the script,
but with .config appended. So if the script is located at /etc/snmp/smart, the config file will be `/etc/snmp/smart.config`. Alternatively you can also specific a config via `-c`.

- Anything starting with a # is comment. 
- variables is $variable=$value.  
- Empty lines are ignored. 
- Spaces and tabes at either the start or end of a line are ignored. 
- Any line with out a matched variable or # are treated as a disk.

```bash
#This is a comment
cache=/var/cache/smart
smartctl=/usr/bin/env smartctl
useSN=1
ada0
ada1
da5 /dev/da5 -d sat
twl0,0 /dev/twl0 -d 3ware,0
twl0,1 /dev/twl0 -d 3ware,1
twl0,2 /dev/twl0 -d 3ware,2
```

The variables are as below.

| Variable | Default | Description |
|----------|---------|-------------|
| cache    | /var/cache/smart | The path to the cache file to use. |
| smartctl | /usr/bin/env smartctl | The path to use for smartctl. |
| useSN    | 1       | If set to 1, it will use the disks SN for reporting instead of the device name. |

A disk line is can be as simple as just a disk name under /dev/. Such as in the config above
The line `ada0` would resolve to `/dev/ada0` and would be called with no special argument. If a line has a space in it, everything before the space is treated as the disk name and is what used for reporting and everything after that is used as the argument to be passed to `smartctl`.

If you want to guess at the configuration, call it with `-g` and it will print out what it thinks it should be.

6. Restart snmpd on your host

    ```bash
    sudo systemctl restart snmpd
    ```

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

7. Optionally setup nightly self tests for the disks. The exend will
   run the specified test on all configured disks if called with the
   `-t` flag and the name of the SMART test to run.

    ```
    0 0 * * * /etc/snmp/smart -t long
    ```
