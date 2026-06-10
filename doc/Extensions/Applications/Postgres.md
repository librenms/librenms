# Postgres

## SNMP Extend

1. Copy the shell script, postgres, to the desired host

    ```
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/postgres -O /etc/snmp/postgres
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/postgres
    ```

3. Edit your snmpd.conf file and add:

    ```bash
    extend postgres /etc/snmp/postgres
    ```

4. Restart snmpd on your host

5. Install the Nagios check `check_postgres.pl` on your system:
<https://github.com/bucardo/check_postgres>

6. Verify the path to `check_postgres.pl` in `/etc/snmp/postgres` is
correct.

7. (Optional) If you wish to change the DB username (default: `pgsql`), enable
the postgres DB in totalling (e.g. set ignorePG to 0, default: 1), or set a
hostname for `check_postgres.pl` to connect to (default: the Unix Socket `postgresql` is running on), then create the file `/etc/snmp/postgres.config` with the following contents (note that not all of them need be defined, just whichever you'd like to change):

```
DBuser=monitoring
ignorePG=0
DBhost=localhost
```

Note that if you are using netdata or the like, you may wish to set ignorePG
to 1 or otherwise that total will be very skewed on systems with light or
moderate usage.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.