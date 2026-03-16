
# SMART

Monitor disk SMART health and attributes via a LibreNMS JSON SNMP extend.

The script uses `smartctl --json -a` to collect data and writes cached output files for `snmpd` to read.

## Install prerequisites

`smartctl` must support `--json` (smartmontools >= 7).

=== "Debian/Ubuntu"

    ```bash
    apt-get install smartmontools libjson-perl libmime-base64-perl libio-compress-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install smartmontools p5-JSON p5-MIME-Base64 p5-IO-Compress
    ```

=== "RedHat/CentOS"

    ```bash
    dnf install smartmontools perl-JSON perl-MIME-Base64 perl-IO-Compress
    ```

## SNMP Extend

1. Download the script onto the host.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/smart-v1 -O /etc/snmp/smart
    ```

2. Make it executable.

    ```bash
    chmod +x /etc/snmp/smart
    ```

3. Create the config file. By default it is the script path with `.config` appended.
   If the script is located at `/etc/snmp/smart`, the config file will be `/etc/snmp/smart.config`.
   Alternatively you can specify a config via `-c`.

   To generate a starter config:

    ```bash
    /etc/snmp/smart -g > /etc/snmp/smart.config
    ```

4. (Recommended) Run it periodically (writes cache files, then SNMP only reads them).
   This avoids SNMP timeouts on slow disks.

    === "cron"

        Add to root's crontab (or `/etc/cron.d/snmp-extentions`):

        ```bash
        # SNMP SMART extend, runs every 5 minutes
        */5 * * * * /etc/snmp/smart -u >/dev/null 2>&1
 
        ```

    === "systemd"

        `/etc/systemd/system/snmp-smart-extend.service`

        ```ini
        [Unit]
        Description=LibreNMS SMART SNMP extend cache

        [Service]
        Type=oneshot
        ExecStart=/etc/snmp/smart -u
        ```

        `/etc/systemd/system/snmp-smart-extend.timer`

        ```ini
        [Unit]
        Description=Run LibreNMS SMART extend every 5 minutes

        [Timer]
        OnBootSec=2min
        OnUnitActiveSec=5min
        Persistent=true

        [Install]
        WantedBy=timers.target
        ```

        Enable the timer:

        ```bash
        systemctl daemon-reload
        systemctl enable --now smart-extend.timer
        ```

        Note: `smartctl` often requires elevated privileges to access disks. If you change the service to run as a non-root user, ensure it can run `smartctl` against your devices and can write the cache path.

5. Edit your `snmpd.conf` file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend smart /bin/cat /var/cache/smart.snmp
    ```

6. Restart `snmpd`.

    ```bash
    sudo systemctl restart snmpd
    ```

The application should be auto-discovered as described at the top of the page.
If it is not, follow the steps under the `SNMP Extend` heading on that page.

## Config file format

- Lines starting with `#` are comments.
- Variables use `key=value`.
- Empty lines are ignored.
- Spaces and tabs at either the start or end of a line are ignored.
- Any other line is treated as a disk entry.

Example:

```bash
# This is a comment
cache=/var/cache/smart
smartctl=/usr/bin/env smartctl
useSN=1

# Disk entries:
ada0
ada1
da5 /dev/da5 -d sat
twl0,0 /dev/twl0 -d 3ware,0
twl0,1 /dev/twl0 -d 3ware,1
twl0,2 /dev/twl0 -d 3ware,2
```

Variables:

| Variable | Default | Description |
|----------|---------|-------------|
| cache    | /var/cache/smart | Cache base path. The script writes JSON to `cache` and base64+gzip to `cache.snmp`. |
| smartctl | /usr/bin/env smartctl | Command/path used to run `smartctl`. |
| useSN    | 1 | If set to 1, it will use the disk serial number for reporting instead of the device name. |

Disk entry rules:

- A disk line can be just a device name under `/dev` (for example, `ada0` resolves to `/dev/ada0`).
- If the line contains a space, everything before the first space is used for reporting; everything after is passed to `smartctl`.

To have the script guess disk entries for your system:

```bash
/etc/snmp/smart -g
```

## Optional: schedule SMART self-tests

The script will run a SMART self-test on all configured disks when called with `-t <test>`:

```bash
0 0 * * * /etc/snmp/smart -t long >/dev/null 2>&1
```
