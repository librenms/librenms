# Postgres

## SNMP Extend

1. Copy the shell script, postgres, to the desired host

    ```
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/postgres -O /etc/snmp/postgres
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/postgres
    ```

3. Edit your `snmpd.conf` file and add:

    ```bash
    extend postgres /etc/snmp/postgres
    ```

4. Restart snmpd on your host.

5. Install the Nagios check `check_postgres.pl` on your system:
<https://github.com/bucardo/check_postgres>

6. Verify the path to `check_postgres.pl` in `/etc/snmp/postgres` is
correct.

7. (Optional) If you wish to change the DB username (default: `pgsql`), enable
the postgres DB in the total. Set ignorePG to 0. The default is 1. You can also set a
hostname for `check_postgres.pl` to connect to (default: the Unix Socket `postgresql` is running on), then create the file `/etc/snmp/postgres.config` with the following contents (note that not all of them are necessary. Give only the values to change):

```
DBuser=monitoring
ignorePG=0
DBhost=localhost
```

With netdata or a similar tool, set `ignorePG` to 1. Without this
setting, the total is wrong on a system with light or
moderate usage.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.