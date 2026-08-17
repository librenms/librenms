# AdGuard Home

A python script that gets query and blocking statistics from the
[AdGuard Home](https://github.com/AdguardTeam/AdGuardHome) REST API and
exports them with SNMP Extend. It graphs DNS queries vs blocked queries,
the blocked-query breakdown (filters, safe browsing, safe search,
parental), average processing time, and the running/protection state.

## SNMP Extend

1. Download the script onto the AdGuard Home host

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/adguard -O /etc/snmp/adguard
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/adguard
    ```

3. Create the config file `/etc/snmp/adguard` reads, `/etc/snmp/adguard.json`,
    with the base URL of the AdGuard Home web interface and the credentials of
    a web UI user:

    ```json
    {
        "url": "http://127.0.0.1:3000",
        "username": "admin",
        "password": "secret"
    }
    ```

    Optional keys: `timeout` (seconds, default 10) and `insecure` (set to
    `true` to skip TLS certificate verification on https URLs).

    The file holds credentials, so restrict it to the user snmpd runs
    extend scripts as. On Debian/Ubuntu that is `Debian-snmp`:

    ```bash
    chown root:Debian-snmp /etc/snmp/adguard.json
    chmod 640 /etc/snmp/adguard.json
    ```

4. Edit the snmpd.conf file to include the extend by adding the following line to the end of the config file:

    ```bash
    extend adguard /etc/snmp/adguard
    ```

5. Restart snmpd service on the host

    LibreNMS discovers the application automatically. Its statistics then
    appear on the Apps page of the host. Note: the applications module
    must be enabled on the host or globally.
