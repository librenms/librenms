# Fail2ban

A small shell script that checks your system's fail2ban status.

## SNMP Extend

1.  Copy the shell script, fail2ban, to the desired host.

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/fail2ban -O /etc/snmp/fail2ban
    ```

2.  Make the script executable

    ```bash
    chmod +x /etc/snmp/fail2ban
    ```

3.  Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend fail2ban /etc/snmp/fail2ban
    ```

    1.  If you want to use the cache, it is as below, by using the -c switch.

        ```bash
        extend fail2ban /etc/snmp/fail2ban -c
        ```

    2.  If you want to use the cache and update it if needed, this can by using the `-c` and `-U` switches.

        ```bash
        extend fail2ban /etc/snmp/fail2ban -c -U
        ```

    3.  If you need to specify a custom location for the fail2ban-client, that can be done via the `-f` switch.

        ```bash
        extend fail2ban /etc/snmp/fail2ban -f /foo/bin/fail2ban-client
        ```

        If not specified, `/usr/bin/env fail2ban-client` is used.

1. Restart snmpd on your host

    ```bash
    sudo systemctl restart snmpd
    ```
2. If you wish to use caching, add the following to /etc/crontab and
restart cron.

    The following will update the cache every 3 minutes.

    ```cron
    */3    *    *    *    *    root    /etc/snmp/fail2ban -u
    ```

If you have more than a few jails configured, you may need to use
caching as each jail needs to be polled and fail2ban-client can't do
so in a timely manner for than a few. This can result in failure of
other SNMP information being polled.

For additional details of the switches, please see the POD in the
script it self at the top.