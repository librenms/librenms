
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

3. Make the script executable.

```bash
chmod +x /etc/snmp/smart
```

4. Add a cronjob for the script. A slow disk then does not
   result in errors.

```bash
 */5 * * * * /etc/snmp/smart -u
```

5. Edit your `snmpd.conf` file and add:

```bash
extend smart /bin/cat /var/cache/smart.snmp
```

6. You must also create the config file. Its default path is the path of the
   script, but with .config appended. So if the script is located at /etc/snmp/smart, the
   config file is `/etc/snmp/smart.config`. You can also give a
   config via `-c`.


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

A disk line can hold only a disk name under `/dev/`. The config
above, the line `ada0` gives `/dev/ada0`. The script calls it with no special
argument. If a line has a space in it, everything before the space is treated as the disk
name and is what used for reporting and everything after that is used as the argument to
be passed to `smartctl`.

To get an estimate of the configuration, call the script with `-g`. It
then prints its own estimate.

6. Restart snmpd on your host.

    ```bash
    sudo systemctl restart snmpd
    ```

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.

7. Optionally, add nightly self tests for the disks. The extend then
   run the specified test on all configured disks if called with the
   `-t` flag and the name of the SMART test to run.

    ```
    0 0 * * * /etc/snmp/smart -t long
    ```
