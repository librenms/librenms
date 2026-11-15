# AdGuard Home

A python script that gets query and blocking statistics from the
[AdGuard Home](https://github.com/AdguardTeam/AdGuardHome) REST API and
exports them with SNMP Extend. It graphs DNS queries vs queries blocked by filters,
the blocked-query breakdown (filters, safe browsing, safe search,
parental), average processing time, and the running/protection state.

Values are totals over AdGuard's configured statistics interval (24 hours
by default), not per-second rates. Changing that interval in AdGuard
will rescale the graphs.

The `blocked` and `block_pct` metrics count queries blocked by filters only;
safe browsing, safe search, and parental replacements are graphed separately.
`block_pct` is 0 when there are no DNS queries in the statistics interval.

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

    Optional keys: `timeout` (seconds, default 1) and `insecure` (set to
    `true` to skip TLS certificate verification on https URLs). LibreNMS
    defaults to a 1 second SNMP timeout. Local AdGuard answers well inside
    that. If the API is remote or polls come back empty, cache via cron
    instead of raising the SNMP timeout (see below).

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

    If a live extend times out, add this to root's crontab and have snmpd
    cat the cache. Write to a temporary file and rename it so polls see a
    complete result while the next API request is running:

    ```
    */5 * * * * /etc/snmp/adguard > /var/cache/adguard.tmp; mv /var/cache/adguard.tmp /var/cache/adguard
    ```

    The `;` also publishes the script's error output when an API request
    fails, so LibreNMS receives the failure instead of stale statistics.
    Run the command once to populate the cache, ensure snmpd can read it,
    and replace the live extend line with:

    ```
    extend adguard /bin/cat /var/cache/adguard
    ```

5. Restart snmpd service on the host

    LibreNMS discovers the application automatically. Its statistics then
    appear on the Apps page of the host. Note: the applications module
    must be enabled on the host or globally.
